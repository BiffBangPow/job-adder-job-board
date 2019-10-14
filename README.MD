##Sync
- Any Locations, Countries, Categories, Sub categories, Job types, or Currencies will only be synced if they are associated with a job


##Cleanup
The cleanup  will delete in this order:
- Jobs that have passed their expiry date
- Jobs that have not been updated in over 1 hours (so have not been present in the API calls and therefore have been removed from Job Adder)
- Any Locations, Countries, Categories, Sub categories, Job types, or Currencies not associated with any jobs

##Job alerts
