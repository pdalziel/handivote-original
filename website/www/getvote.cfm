<cfif not IsDefined("cardnum")>
<cflocation url="index.cfm">
</cfif>

<cftry>
<cfquery datasource="handivote" name="getvote">
select questionoption.option
from vote, questionoption
where barcode = '#cardnum#'
and vote.optionID = questionoption.id
and vote.refid=#refid#
</cfquery>
<cfif getvote.RecordCount gt 0>
<cfoutput>#getvote.option#</cfoutput>
<cfelse>


<cfquery datasource="handivote" name="getvote">
select *
from bad_vote
where barcode = '#cardnum#'
and refid=#refid#
</cfquery>

<cfif getvote.RecordCount gt 0>
<cfoutput>Card Number Invalid</cfoutput>
<cfelse>
<cfoutput>No Vote</cfoutput>
</cfif>
</cfif>
<cfcatch>
<cfoutput>No Vote #cfcatch.message#

select option
from vote, questionoption
where barcode = '#cardnum#'
and vote.optionID = questionoption.id
and vote.refid=#refid#
 </cfoutput>
</cfcatch>
</cftry>
