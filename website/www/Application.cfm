<cfsetting enablecfoutputonly="Yes">
<cfapplication name="Handivote"
			   clientmanagement="Yes"
			   sessionmanagement="Yes"
			   clientStorage = "cookie"
			   sessiontimeout="#CreateTimeSpan(99,99,90,0)#"
			   applicationtimeout="#CreateTimeSpan(0,0,90,0)#">
			   

<cfset Application.Title = "Handivote">
<cfsetting showDebugOutput="No">
<cfset datasource="handivote">










 
	




	
	

