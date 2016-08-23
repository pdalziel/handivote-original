<cfif not IsDefined("qnumber") or not IsDefined("voted") or not isDefined("answer")>
<cflocation url="index.cfm">
</cfif>

<cftry>
<cfquery datasource="handivote">
insert into answers
set qnumber=#qnumber#,
answer="#answer#",
voted="#voted#"
</cfquery>
<cfcatch>
<cfoutput>ERROR #cfcatch.message#</cfoutput>
</cfcatch>
</cftry>
