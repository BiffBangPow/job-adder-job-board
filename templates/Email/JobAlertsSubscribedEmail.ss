<p>To $jobAlertSubscription.Name</p>

<p>You are now subscribed to job alerts from $brandName</p>

<% if $jobAlertSubscription.Description != '' %>
    <p>Your job alerts will contain jobs matching the following criteria:</p>
    $jobAlertSubscription.Description.RAW
<% end_if %>

<p><a href="$jobAlertSubscription.UnsubscribeLink">Unsubscribe</a> | <a href="$jobAlertSubscription.UpdateSubscriptionLink">Update subscription</a></p>