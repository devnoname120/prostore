#include <stdio.h>
#include <string.h>

#include <pspsdk.h>
#include <pspkernel.h>
#include <pspdebug.h>
#include <pspctrl.h>
#include <pspthreadman.h>
#include <psprtc.h>
#include <psputility.h>

#include <pspdisplay.h>
#include <pspgu.h>
#include <oslib/oslib.h>

#include <pspnet.h>
#include <pspnet_inet.h>
#include <pspnet_apctl.h>
#include <pspnet_resolver.h>
#include <psputility_netmodules.h> 
#include <psputility_netparam.h>
#include <pspwlan.h>

#include <psphttp.h>

// Global vars
// static char resolverBuffer[1024];
// static int resolverId;


int Wlan_Start()
{
	// Error handler
	int err = 0;
	
	 err = sceUtilityLoadNetModule(PSP_NET_MODULE_COMMON);
	  if (err != 0)
	  {
		#ifdef DEBUG_MODE
		printf("Error, could not load PSP_NET_MODULE_COMMON returned %08X\n", err);
		#endif
		return -1;
	  }
	  
	  err = sceUtilityLoadNetModule(PSP_NET_MODULE_INET);
	  if (err != 0)
	  {
		#ifdef DEBUG_MODE
		printf("Error, could not load PSP_NET_MODULE_INET returned %08X\n", err);
		#endif
		return -2;
	  }
	
	// Init the Inet library
	err = sceNetInit(128*1024, 42, 4*1024, 42, 4*1024);
	err += sceNetInetInit();
	err += sceNetApctlInit(0x8000, 48);
	if(err != 0) return -3;
	
	// Create a resolver (resolver is already inited by pspSdkInetInit)
	// sceNetResolverCreate(&resolverId, resolverBuffer, sizeof(resolverBuffer));
	// if(err)
	// {
	// 	printf("Error, sceNetResolverInit returned %08X\n", err);
	// 	return -4;
	// }
	
	return 0;
}

int Wlan_Stop()
{
	// Disconnect from access point
	sceNetApctlDisconnect();
	
	// Delete the resolver
	// sceNetResolverDelete(resolverId);
	
	// Unload the inet libraries
	sceNetApctlTerm();
	sceNetInetTerm();
	sceNetTerm();
	
	/* //Seems to be useless to unload them
	// Stop Net related modules
	sceUtilityUnloadNetModule(PSP_NET_MODULE_COMMON);
	sceUtilityUnloadNetModule(PSP_NET_MODULE_INET);
	*/
	
	// We did our best to close the library
	return 0;
}


int Wlan_Connect(int configid)
{
	sceNetApctlConnect(configid);
	
	int state = 0;
	
	while (1) {
		// Wait a bit
		sceKernelDelayThread(200*1000);

		// Get the current state
		int err = sceNetApctlGetState(&state);
		#ifdef DEBUG_MODE
		printf("Wireless connnection state:%d\n", state);
		#endif
		if (err != 0 || state == 0)
		{
			// Connection failed
			return -1;
		}

		if (state == 4)
		{
			// Connection established
			return 0;
		}
	}
}

int Wlan_ConnectDialog()
{

   	pspUtilityNetconfData data;

	memset(&data, 0, sizeof(data));
	data.base.size = sizeof(data);
	data.base.language = PSP_SYSTEMPARAM_LANGUAGE_ENGLISH;
	// Western button X
	data.base.buttonSwap = PSP_UTILITY_ACCEPT_CROSS;
	data.base.graphicsThread = 17;
	data.base.accessThread = 19;
	data.base.fontThread = 18;
	data.base.soundThread = 16;
	data.action = PSP_NETCONF_ACTION_CONNECTAP;
	
	// Dummy parameter
	struct pspUtilityNetconfAdhoc adhocparam;
	memset(&adhocparam, 0, sizeof(adhocparam));
	data.adhocparam = &adhocparam;

	sceUtilityNetconfInitStart(&data);
	
	int running = 1;
	while(running)
	{

		switch(sceUtilityNetconfGetStatus())
		{
			case PSP_UTILITY_DIALOG_NONE:
				break;

			case PSP_UTILITY_DIALOG_VISIBLE:
				sceUtilityNetconfUpdate(1);
				break;

			case PSP_UTILITY_DIALOG_QUIT:
				sceUtilityNetconfShutdownStart();
				break;
				
			case PSP_UTILITY_DIALOG_FINISHED:
				running = 0;
				break;

			default:
				break;
		}

		sceDisplayWaitVblankStart();
		sceGuSwapBuffers();
	}
	// Make sure we are connected
	int state;
	
	if (sceNetApctlGetState(&state) == 0)
	{
		return  0;
	}
	else
	{
		return -1;
	}
}

// ------------------------------------------------------------------------------------------------------------------
// Here begins the http part
// ------------------------------------------------------------------------------------------------------------------

// HTTP part is DEPRECATED: we now use the libcurl

int httptemplate;
int connectionid;

int Http_Start()
{
	int err = 0;
	err = sceUtilityLoadNetModule(PSP_NET_MODULE_PARSEURI);
	if (err != 0)
	{
		printf("Error, couldn't load PSP_NET_MODULE_PARSEURI returned %08X\n", err);
		return -1;
	}
	
	err = sceUtilityLoadNetModule(PSP_NET_MODULE_PARSEHTTP);
	if (err != 0)
	{
		printf("Error, couldn't load PSP_NET_MODULE_PARSEHTTP returned %08X\n", err);
		return -1;
	}
	err = sceUtilityLoadNetModule(PSP_NET_MODULE_HTTP);
	if (err != 0)
	{
		printf("Error, couldn't load PSP_NET_MODULE_HTTP returned %08X\n", err);
		return -1;
	}
	
	err = sceHttpInit(20000);
	if (err < 0)
	{
		printf("Error, sceHttpInit returned %08X\n", err);
		return -1;
	}
	
	httptemplate = sceHttpCreateTemplate("prostore", 1, 0);
	if (httptemplate < 0)
	{
		return -2;
	}
	
	return 0;
}

int Http_Stop()
{
	sceHttpDeleteTemplate(httptemplate);
	return sceHttpEnd();
}

int Http_Connect(char *address)
{
	return sceHttpCreateConnectionWithURL(httptemplate, address, 0);
}

int Http_Disconnect(int connectionid)
{
	sceHttpDeleteConnection(connectionid);
	
	return 0;
}

int Http_Request(int connectionid, char *address, char *answer, unsigned int answersize)
{
	int requestid = sceHttpCreateRequestWithURL(connectionid, PSP_HTTP_METHOD_GET, address, 0);
	if (requestid < 0)
	{
		return -1;
	}
	
	int ret = sceHttpSendRequest(requestid, NULL, 0);
	if (ret < 0)
	{
		return -2;
	}
	
	do
	{
		int ret = sceHttpReadData(requestid, answer, answersize);
		if (ret < 0)
		{
			return -3;
		}
	}
	while (ret > 0);
	
	sceHttpDeleteRequest(requestid);
	
	return 0;
}