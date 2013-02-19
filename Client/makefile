TARGET = ProStore

#Allows to compile a debug version with debug messages
ifeq ($(DEBUG_MODE), 1)
USER_FLAGS=-DDEBUG_MODE
DEBUG_PARAMETER=-pg
endif

LIB_MXML = deps/mXml/mxml-search.o deps/mXml/mxml-set.o deps/mXml/mxml-string.o deps/mXml/mxml-attr.o deps/mXml/mxml-entity.o deps/mXml/mxml-file.o deps/mXml/mxml-get.c deps/mXml/mxml-index.o deps/mXml/mxml-node.o deps/mXml/mxml-private.o

OBJS = main.o networking.o prostorebridge.o xmlwrapper.o
OBJS += $(LIB_MXML)


BUILD_PRX = 1
PSP_FW_VERSION=371

INCDIR = 
CFLAGS = -G0 -Wall -O0 $(USER_FLAGS) $(DEBUG_PARAMETERS)
CXXFLAGS = $(CFLAGS) -fno-exceptions -fno-rtti
ASFLAGS = $(CFLAGS)

LIBDIR =
LDFLAGS =
MYLIBS=

LIB_CURL = -lcurl
# minpspw's mxml library is outdated
#LIB_MXML = -lmxml
STDLIBS= -losl -lpng -ljpeg -lz \
         -lpsphprm -lpspsdk -lpspctrl -lpspumd -lpsprtc -lpsppower -lpsputility -lpspgum -lpspgu -lm -lpspvram -lpspaudiolib -lpspaudio -lpsphttp -lpspssl -lpspwlan \
         -lpspnet_adhocmatching -lpspnet_adhoc -lpspnet_adhocctl $(LIB_CURL)$(DEBUG_LIBS)
LIBS=$(STDLIBS)$(MYLIBS)


EXTRA_TARGETS = EBOOT.PBP
PSP_EBOOT_TITLE = Pro Store
#PSP_EBOOT_ICON = ICON0.png

PSPSDK=$(shell psp-config --pspsdk-path)
include $(PSPSDK)/lib/build.mak

clean:
	rm --recursive *.o *.PBP *.prx *.elf *.SFO