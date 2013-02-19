int Wlan_Start();
int Wlan_Stop();
int Wlan_Connect(int configid);
int Wlan_ConnectDialog();

// -----------------------------------------------------------

// DEPRECATED: we now use the libcurl
int Http_Start();
int Http_Stop();
int Http_Connect(char *address); // Returns connnectionid
int Http_Disconnect(int connectionid);
int Http_Request(int connectionid, char *address, char *answer, unsigned int answersize);