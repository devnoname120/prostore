#include <stdio.h>
#include <stdlib.h>
#include "deps/mxml/mxml.h"

#include "prostorebridge.h"
#include "xmlwrapper.h"


#define INITIAL_HB_LIST_SIZE 200
int HbListFromXML(struct hb_entry ***pHomebrew_list, char *xmlString, int *homebrews_count)
{
    // TODO: check failure
    struct hb_entry **homebrew_list = (struct hb_entry **) malloc(sizeof(struct hb_entry *) * INITIAL_HB_LIST_SIZE);
    
    printf("hb_list: %08X", homebrew_list);
    // Check the maximum number of elements we can store: useless since we realloc data
    // int maxElem = ipart(arraySize / sizeof(struct hb_entry));
    
    //struct hb_entry *homebrew_list =(struct hb_entry*)malloc(sizeof(struct hb_entry));
    //struct hb_entry *homebrew_list = NULL;
    
    
    mxml_node_t *tree;
    
    tree = mxmlLoadString(NULL, xmlString, MXML_OPAQUE_CALLBACK);
    if (tree != NULL)
    {
        mxml_node_t *primary_node;
        
        // If there is "prostory_entry" in the xml tree
        if ((primary_node = mxmlFindElement(tree, tree, "prostore_entry", NULL, NULL, MXML_DESCEND_FIRST)) != NULL)
        {
            mxml_node_t *current_node;
            current_node = primary_node;
            
            
            int i = 0;
            
            // Step through all hb entries
            
            while ((current_node = mxmlFindElement(current_node, tree, "hbentry", NULL, NULL, MXML_DESCEND)) != NULL)
            {
                // If the array of pointers is too small
//commented for simplicity
//                if (i>homebrew_list_s-1) *homebrew_list = realloc(*homebrew_list, homebrew_list_s + 200);
                
                // TODO: check failure
                struct hb_entry *currentry = (struct hb_entry *) malloc(sizeof(struct hb_entry));
				memset(currentry, 0, sizeof(struct hb_entry));
                
                #ifdef DEBUG_MODE
                printf("Address currentry holds: %08X\n", currentry);
                #endif
                
                currentry->hbid = (char *)mxmlElementGetAttr(current_node, "hbid");
				*currentry->hbid = (int)strtod(currentry->hbid, NULL);
                #ifdef DEBUG_MODE
                printf("Hbid: %d\n", *currentry->hbid);
                #endif
                mxml_node_t *currname_node = mxmlFindElement(current_node, tree, "hbname", NULL, NULL, MXML_DESCEND);
                currentry->hbname = (char *)mxmlGetOpaque(currname_node);
                #ifdef DEBUG_MODE
                printf("Hbname: %s\n", currentry->hbname);
                #endif                
                currname_node = mxmlFindElement(current_node, tree, "hbcategory", NULL, NULL, MXML_DESCEND);
                currentry->hbcategory = (char *)mxmlGetOpaque(currname_node);
                #ifdef DEBUG_MODE
                printf("Hbcategory: %s\n", currentry->hbcategory);
                #endif                    
                currname_node = mxmlFindElement(current_node, tree, "hbversion", NULL, NULL, MXML_DESCEND);
                currentry->hbversion = (char *)mxmlGetOpaque(currname_node);        
                #ifdef DEBUG_MODE
                printf("Hbversion: %s\n", currentry->hbversion);
                #endif
                currname_node = mxmlFindElement(current_node, tree, "hbrelease", NULL, NULL, MXML_DESCEND);
                currentry->hbrelease = (char *)mxmlGetOpaque(currname_node);    
                #ifdef DEBUG_MODE
                printf("Hbrelease: %s\n", currentry->hbrelease);
                #endif        
                currname_node = mxmlFindElement(current_node, tree, "hbvotescount", NULL, NULL, MXML_DESCEND);
                currentry->hbvotescount = (char *)mxmlGetOpaque(currname_node);
                #ifdef DEBUG_MODE
                printf("hbvotescount %s\n", currentry->hbvotescount);
                #endif
                currname_node = mxmlFindElement(current_node, tree, "hbdlcount", NULL, NULL, MXML_DESCEND);
                currentry->hbdlcount = (char *)mxmlGetOpaque(currname_node);
                #ifdef DEBUG_MODE
                printf("hbdlcount %s\n", currentry->hbdlcount);
                #endif                
                currname_node = mxmlFindElement(current_node, tree, "hbsdescription", NULL, NULL, MXML_DESCEND);
                currentry->hbsdescription = (char *)mxmlGetOpaque(currname_node);            
                #ifdef DEBUG_MODE
                printf("hbsdescription %s\n", currentry->hbsdescription);
                #endif    
                currname_node = mxmlFindElement(current_node, tree, "hbldescription", NULL, NULL, MXML_DESCEND);
                currentry->hbldescription = (char *)mxmlGetOpaque(currname_node);
                #ifdef DEBUG_MODE
                printf("hblDescription %s\n", currentry->hbldescription);
                #endif
                
                homebrew_list[i] = currentry;
                i++;
            }
            
            #ifdef DEBUG_MODE
            printf("%d items\n", i+1);
            #endif
            *homebrews_count = i; // Save the number of items
            
            printf("%08X\n", *homebrew_list);
            *pHomebrew_list = homebrew_list;
        }
        else
        {
            // No <prostore_entry>
            return -2;
        }
    }
    else
    {
        // Couldn't load string
        return -1;
    }
    return 0;
}