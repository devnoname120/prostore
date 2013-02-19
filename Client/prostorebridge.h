#include <oslib/oslib.h>
#include <curl/curl.h>


enum image_state
{
	IMAGE_NONE,
	IMAGE_LOADING,
	IMAGE_ERROR,
	IMAGE_LOADED
};

struct hb_icon
{
	enum image_state state;
	OSL_IMAGE *icon;
};

struct hb_screenshot
{
	enum image_state state;
	OSL_IMAGE *screenshot;
};

struct hb_entry
{
	struct hb_icon icon;
	// Points to an array of pointers to screenshot structs
	struct hb_screenshot **aScreenshots;
	
	int *hbid;
	char *hbname;
	char *hbcategory;
	float *hbversion;
	char *hbrelease;
	int *hbvotescount;
	int *hbdlcount;
	// Short description
	char *hbsdescription;
	// Long description
	char *hbldescription;
	
};


int PB_Init();
int PB_DeInit();
int PB_GetHbList(struct hb_entry ***hb_list, int *homebrews_count);
enum image_state PB_CheckScreenshot(struct hb_entry *hb_entry, int screenshotNr);
int PB_FreeScreenshotStruct(struct hb_entry *hb_entry, int screenshotNr);
int PB_GetScreenshot(struct hb_entry *hb_entry, int screenshotNr);
int PB_GetIcon(struct hb_entry *hb_entry);
