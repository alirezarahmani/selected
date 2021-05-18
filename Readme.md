###structre
######CODE regards
- we add doctrine:extension for customizing get opration
- we add subscriber for customizing post
- we add normalizer for customizing read output
- other are base symfonya and api-platform docs

------------
######system regards
time zone are load from available php timezone

-----
we try to save all datetime like scheduled and worked or deserved
holiday in minutes and with normalizer convert them to 
hour and minutes when user read from this table

-----
###timeOff 
we have 3 type of time 0ff **holiday  sick  unpaid**
deserved holiday for employee only shows 
how many days hour and minutes 
user can have holiday and **not** decrease or increase
 by getting and cancelling timeOffHoliday or change fixedDays
----
###caculation in timesheets
unpaid breaks deduct from scheduled 
the real time user goes to break deduct from its worked
if no unpaid break scheduled ,break count in worked
user should be able to clockin and clock out when ever they want and if no shift
scheduled in this time all worked time calculate as OT

**total**=(clockout-clockIn)-(break) 

**worked**=(clockout-clockIn) 

**scheduled**=(endShift-startShift)-unpaidBreak

**overtime**=worked-scheduled

**labor**=worked*baseHourlyRate+overtime*payrollOt


#payment
for billing payment should first request to /api/business_banks/exchnage_billing_cost
to know your selected billing rate in your bank currency then in payment the amount thate be entered o pay should be response of above api
#your on time?
your on time if you attend on all of your shift and dont late more than 5 minutes
