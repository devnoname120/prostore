#include <stdio.h>

//#include "deps/mxml/mxml.h"

#include <pspkernel.h>

#include <pspctrl.h>
#include <pspthreadman.h>
#include <psprtc.h>
#include <psputility.h>
#include <pspgu.h>

#include <oslib/oslib.h>

#include <pspsdk.h>

#include "prostorebridge.h"
#include "main.h"
#include "networking.h"

PSP_MODULE_INFO("Pro Store", 0, 1, 1);
PSP_MAIN_THREAD_ATTR(THREAD_ATTR_USER|THREAD_ATTR_VFPU);

// Avoids a bug in the pspsdk - Thanks to hrydgard
//unsigned int sce_newlib_heap_kb_size = 10000;
PSP_HEAP_SIZE_KB(12*1024);





/* Exit callback */
int exit_callback(int arg1, int arg2, void *common)
{
   sceKernelExitGame();
   return 0;
}

/* Callback thread */
int CallbackThread(SceSize args, void *argp)
{
   int cbid;

   cbid = sceKernelCreateCallback("Exit Callback", exit_callback, NULL);
   sceKernelRegisterExitCallback(cbid);
   sceKernelSleepThreadCB();

   return 0;
}

/* Sets up the callback thread and returns its thread id */
int SetupCallbacks(void)
{
   int thid = 0;

   thid = sceKernelCreateThread("update_thread", CallbackThread, 0x11, 0xFA0, PSP_THREAD_ATTR_USER, 0);
   if(thid >= 0)
   {
      sceKernelStartThread(thid, 0, 0);
   }

   return thid;
}


int main()
{

	Wlan_Start();

	
	// Init the oslib + graphics (must be after wlan and http)
	oslInit(0);
	oslInitGfx(OSL_PF_8888, 1);
	
	// In networking.h
	Wlan_Connect(3);
	
	// Init the Prostore Bridge part
	PB_Init();
	
	// Pointer on an array containing hb_entry structs
	struct hb_entry **hb_list = NULL;
	int homebrews_count = 0;
	
	// Update the pointer
	PB_GetHbList(&hb_list, &homebrews_count);
	printf("hb_list %08X\n", hb_list);
	int quit=0;
	
	int cursor = 0;
	enum menu_state menu = MAIN_MENU;
	
	
	while (!quit)
	{
		oslStartDrawing();
		oslClearScreen(RGB(255,255,255));
		
		oslReadKeys();
		quit = osl_keys->held.start;
		
		if (menu == MAIN_MENU)
		{
			if (osl_keys->pressed.cross) menu = VIEW_HOMEBREW;
		
			if (osl_keys->pressed.down && cursor < homebrews_count - 1) cursor += 1;
			if (osl_keys->pressed.up && cursor > 0) cursor -= 1;
			
			printf("homebrews_count: %d\n", homebrews_count);
			int i=0;
			for (;i < homebrews_count; i++)
			{
				struct hb_entry *currhb = (struct hb_entry *)hb_list[i];
				
				if (i==cursor)
				{
					oslSetTextColor(RGB(255,0,0));
				}
				else
				{
					oslSetTextColor(RGB(255,255,255));
				}
				printf("i=%d\n", i);
				printf("Suspens\n");
				printf("currhb %08X\n", currhb);
				printf("currhb->hbname %08X\n", currhb->hbname);
				printf("String pointed by currhb->hbname %s\n", currhb->hbname);
				if (currhb!=NULL && currhb->hbname != NULL) oslDrawString(0, i*osl_curFont->charHeight, currhb->hbname);
				printf("Drawn\n");
			}
		}
		if (menu == VIEW_HOMEBREW)
		{
			if (osl_keys->pressed.triangle) menu = MAIN_MENU;
			
			if (osl_keys->pressed.R)
			{
				menu = VIEW_SCREENSHOT;
			}
			
			struct hb_entry *currhb = (struct hb_entry *)hb_list[cursor];
			
			oslSetTextColor(RGB(255,255,255));
			
			int colnumber = 0;
			
			oslDrawStringf(0, colnumber*osl_curFont->charHeight, "%d", *currhb->hbid);
			colnumber += 1;
			oslDrawString(0, colnumber*osl_curFont->charHeight, currhb->hbname);
			colnumber += 1;
			oslDrawString(0, colnumber*osl_curFont->charHeight, currhb->hbcategory);
			colnumber += 1;
			oslDrawString(0, colnumber*osl_curFont->charHeight, currhb->hbversion);
			colnumber += 1;
			oslDrawString(0, colnumber*osl_curFont->charHeight, currhb->hbrelease);
			colnumber += 1;
			oslDrawString(0, colnumber*osl_curFont->charHeight, currhb->hbvotescount);
			colnumber += 1;
			oslDrawString(0, colnumber*osl_curFont->charHeight, currhb->hbdlcount);
			colnumber += 1;
			oslDrawString(0, colnumber*osl_curFont->charHeight, currhb->hbsdescription);
			colnumber += 1;
			oslDrawString(0, colnumber*osl_curFont->charHeight, currhb->hbldescription);
			colnumber += 1;
			
		}
		printf("VIEW_SCREENSHOT\n");
		if (menu == VIEW_SCREENSHOT)
		{
			struct hb_entry *currhb = (struct hb_entry *)hb_list[cursor];
			
			printf("Dead test\n");
			
			int screenshot_check = PB_CheckScreenshot(currhb, 1);
			if (screenshot_check != IMAGE_ERROR)
			{
				if (screenshot_check != IMAGE_LOADED)
				{
					printf("Needs the screenshot\n");
					PB_GetScreenshot(currhb, 1);
				}
				printf("What I tought\n");
				
				struct hb_screenshot *currsc = NULL;
				// Get a ptr to the screenshot
				if (currhb->aScreenshots != NULL) currsc = (struct hb_screenshot *)currhb->aScreenshots[0];
				
				// Display it
				printf("currscreenshot has: %08X\n", currsc);
				if (currsc != NULL && currsc->state == IMAGE_LOADED) oslDrawImage(currsc->screenshot);
				
			}	
			if (osl_keys->pressed.triangle) menu = VIEW_HOMEBREW ;
		}
		oslEndDrawing();
		oslSyncFrame();
	}
	
	oslEndGfx();
	oslQuit();
	oslNetTerm();
	sceKernelExitGame();
	
	/*ret = Http_Stop();
	printf("Http_Stop returns %d", ret);
	Wlan_Stop();*/
	
	
    return 0;
}  