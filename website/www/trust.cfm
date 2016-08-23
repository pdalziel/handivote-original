<cfif not IsDefined("option")>

<cflocation url="index.cfm">
</cfif>

<cftry>
<cfquery datasource="handivote">
insert into itrust
set opt=#option#,
day=#Day(Now())#,
month=#Month(Now())#,
year=#Year(Now())#,
hour=#Hour(Now())#,
minute=#Minute(Now())#
</cfquery>
<cfcatch>
<cfoutput>ERROR #cfcatch.message#

insert into itrust
set opt=#option#,
day=#Day(Now())#,
month=#Month(Now())#,
year=#Year(Now())#,
hour=#Hour(Now())#,
minute=#Minute(Now())#
</cfoutput>
</cfcatch>
</cftry>
