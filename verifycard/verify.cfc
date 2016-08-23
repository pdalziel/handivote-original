<cfcomponent>
  <cffunction name="verify"
    access="remote"
    returntype="string"
    output="no">

    <cfargument name="barcode"
      type="string"
      required="yes">
    <cfargument name="pin"
      type="string"
      required="yes">

    <cfset Var response="0">

    <cfquery name="getstudent" datasource="xxx">
        select * from cards  where barcode="#barcode#" and pin="#pin#"
    </cfquery>

        <cfif #getstudent.RecordCount# gt 0>
                <cfset response="1">
        </cfif>


    <cfreturn "#response#">
  </cffunction>
</cfcomponent>
