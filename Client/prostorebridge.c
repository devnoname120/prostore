#include <stdio.h>
#include <stdlib.h>
#include <string.h>


#include "networking.h"
#include "prostorebridge.h"
#include "xmlwrapper.h"


char prostore_address[] = "http://devnoname120.kegtux.org/prostore/";

// Init by PB_Init()
CURL *curl;

// Custom write flow function (allows to write answer to memory instead of a file)
struct RequestStruct
{
  char *memory;
  size_t size;
};
 
static size_t WriteMemoryCallback(void *contents, size_t size, size_t nmemb, void *userp)
{
  size_t realsize = size *nmemb;
  struct RequestStruct *mem = (struct RequestStruct *)userp;
 
  mem->memory = realloc(mem->memory, mem->size + realsize + 1);
  if (mem->memory == NULL)
  {
    exit(EXIT_FAILURE);
  }
 
  memcpy(&(mem->memory[mem->size]), contents, realsize);
  mem->size += realsize;
  mem->memory[mem->size] = 0;
 
  return realsize;
}
// End of content getter function

int PB_Init()
{

	// Init the HTTP library
	curl_global_init(CURL_GLOBAL_ALL);
	curl = curl_easy_init();
	
	if (curl != NULL)
	{
		// Custom function to write content to memory instead of a file
		curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, WriteMemoryCallback);
		
		return 0;
	}
	
	// Couldn't create handle
	return -1;
	
}

int PB_DeInit()
{
	curl_global_cleanup();
	curl_easy_cleanup(curl);
	
	return 0;
}

// DEPRECATED: not needed by curl
/*int PB_Connect()
{
	int ret = Http_Connect(prostore_address);
	
	if (ret >= 0)
	{
		connectionid = ret;
		return 0;
	}
	else
	{
		return -1;
	}
}*/

int PB_GetHbList(struct hb_entry ***hb_list, int *homebrews_count)
{
	#ifdef DEBUG_MODE
	printf("Entered %s\n", __func__);
	#endif
	
	struct RequestStruct xml_struct;

	xml_struct.memory = malloc(1);
	// No data so far
	
	xml_struct.size = 0;
	
	curl_easy_setopt(curl, CURLOPT_WRITEDATA, &xml_struct);
	
	// Default values for test purposes. Need to change them then.
	char hb_category[] = "all";
	int hb_rstart = 0;
	int hb_rend = 20;
	char hb_sortBy[] = "id";
	int desc_bool = 0;
	
	// Build the address of the xml request
	char xml_address[200];
	
	snprintf(xml_address, sizeof(xml_address),"%sAPI/gethblist.php?category=%s&rstart=%d&rend=%d&sortby=%s&desc=%d", prostore_address, hb_category, hb_rstart, hb_rend, hb_sortBy, desc_bool);	
	curl_easy_setopt(curl, CURLOPT_URL, xml_address);
	
	#ifdef DEBUG_MODE
	printf("Requesting\n");
	#endif
	int ret = curl_easy_perform(curl);
	if (ret != 0) return -1;
	
	#ifdef DEBUG_MODE
	printf("Request OK\n");
	#endif

	// Retrieve the list of items
	printf("hb_list: %08X", hb_list);
	HbListFromXML(hb_list, xml_struct.memory, homebrews_count);
	printf("Hblist: %08X", hb_list);
	
	return 0;
	
}
enum image_state PB_CheckScreenshot(struct hb_entry *hb_entry, int screenshotNr)
{
	printf("hb_entry=%08X\n", hb_entry);
	printf("hb_entry->aScreenshots=%08X\n", hb_entry->aScreenshots);
	if (hb_entry->aScreenshots == NULL) return IMAGE_NONE;
	struct hb_screenshot *currsc = hb_entry->aScreenshots[screenshotNr-1];
	
	// If the struct exists
	if (currsc != NULL)
	{
		return currsc->state;
	}
	else
	{
		return IMAGE_NONE;
	}
}
int PB_FreeScreenshotStruct(struct hb_entry *hb_entry, int screenshotNr)
{
	struct hb_screenshot *currsc = hb_entry->aScreenshots[screenshotNr-1];
	// Delete the screenshot
	currsc->state = IMAGE_NONE;
	if (currsc->screenshot != NULL) oslDeleteImage(currsc->screenshot);
	free(currsc);
	
	return 0;
}
int PB_GetScreenshot(struct hb_entry *hb_entry, int screenshotNr)
{
	
	// Read data for curl
	struct RequestStruct screenshot_struct;
	
	// Get some room for the screenshot
	struct hb_screenshot *currsc = malloc(sizeof(struct hb_screenshot));
	memset(currsc, 0, sizeof(struct hb_screenshot));
	
	
	// The server redirects us to the real location of the image
	curl_easy_setopt(curl, CURLOPT_FOLLOWLOCATION, 1);
	
	// Write to buffer instead of a file
	screenshot_struct.memory = malloc(1);
	screenshot_struct.size = 0;
	curl_easy_setopt(curl, CURLOPT_WRITEDATA, &screenshot_struct);
	
	// Define request address
	char screenshot_address[150];
	snprintf(screenshot_address, sizeof(screenshot_address),"%sAPI/getscreenshot.php?hbid=%d&scid=%d", prostore_address, *hb_entry->hbid, screenshotNr);
	curl_easy_setopt(curl, CURLOPT_URL, screenshot_address);
	// Make request
	int ret = curl_easy_perform(curl);
	
	if (ret != CURLE_OK) goto error;
	
	char *content_type;
	int content_type_err = curl_easy_getinfo(curl, CURLINFO_CONTENT_TYPE, &content_type);
	if (content_type_err == CURLE_OK)
	{
		// Get first part of content type: the mime type
		char *mime_type = strtok(content_type, ";");
		
		// If we don't have the good type
		if (strcmp(mime_type, "image/png"))
		{
			goto error;
		}
	}
	
	
	// Create a virtual file in RAM
	char screenshot_ram[50];
	snprintf(screenshot_ram, sizeof(screenshot_ram), "screenshot:/%d_%d.png", *hb_entry->hbid, screenshotNr);
	
	printf("%s\n",screenshot_ram);
	OSL_VIRTUALFILENAME ram_screenshots[] = {{screenshot_ram, screenshot_struct.memory, screenshot_struct.size, &VF_MEMORY}};
	oslAddVirtualFileList(ram_screenshots, 1);

	// Load it
	OSL_IMAGE *screenshot = oslLoadImageFilePNG(screenshot_ram, OSL_IN_RAM, OSL_PF_8888);
	screenshot = NULL;
	printf("%08X\n",screenshot);
	if (screenshot == NULL) goto error;
	
	printf("Screenshot=%08X\n", screenshot);
	
	// Release ressources
	oslRemoveVirtualFileList(&ram_screenshots, 1);
	free(screenshot_struct.memory);
	
	
	// Update information
	currsc->screenshot = screenshot;
	currsc->state = IMAGE_LOADED;
	
	hb_entry->aScreenshots = malloc(sizeof(struct hb_screenshot_entry *));
	memset(hb_entry->aScreenshots, 0, sizeof(struct hb_screenshot_entry *));
	
	// Delete previous screenshot
	if (hb_entry->aScreenshots[screenshotNr-1] != NULL) PB_FreeScreenshotStruct(hb_entry, screenshotNr);
	
	// Update screenshot pointer
	hb_entry->aScreenshots[screenshotNr-1] = (struct hb_screenshot_entry *)currsc;
	return 0;
	
	error:
	
	printf("Error happened\n");
	// Delete previous screenshot
	if (hb_entry->aScreenshots != NULL && hb_entry->aScreenshots[screenshotNr-1] != NULL) PB_FreeScreenshotStruct(hb_entry, screenshotNr);
	printf("Step One\n");
	// Set screenshot to error
	currsc->state = IMAGE_ERROR;
	currsc->screenshot = NULL;
	if (hb_entry->aScreenshots != NULL && hb_entry->aScreenshots[screenshotNr-1] != NULL) hb_entry->aScreenshots[screenshotNr-1] = (struct hb_screenshot_entry *)currsc;
	free(screenshot_struct.memory);
	printf("Success");
	return -1;
	
}
int PB_GetIcon(struct hb_entry *hb_entry)
{/*
	// An icon will never be above 50kb
	char icon_buffer[50 * 1024];
	
	int ret = Http_Request(connectionId, sprintf("%sAPI/geticon.php?hbid=%d", *prostore_address, homebrewId), icon_buffer, sizeof(icon_buffer));
	
	if (ret < 0) goto error;
	
	OSL_VIRTUALFILENAME ram_icon[] = {sprintf("icon:/%d.png", homebrewId), icon_buffer, sizeof(icon_buffer), &VF_MEMORY};
	oslAddVirtualFileList(ram_icon, 1);
	
	icon = oslLoadImageFilePNG(sprintf("icon:/%d.png", homebrewId), OSL_IN_RAM, OSL_PF_8888);
	
	if (icon == NULL) goto error;
	
	oslRemoveVirtualFileList(ram_icon, 1);
	hb_entry->icon->icon = icon;
	hb_entry->icon->state = IMAGE_LOADED;
	
	return 0;
	
	error:
	
	hb_entry->icon->state = IMAGE_ERROR;*/
	return -1;
	
}