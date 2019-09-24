<p>To $jobAlertSubscription.Name</p>

<p>There are your new jobs from $brandName</p>


<% if $jobAlertSubscription.Description != '' %>
    <p>These jobs match your criteria of:</p>
    $jobAlertSubscription.Description.RAW
<% end_if %>

<% loop $jobs %>
    <h3><a href="$Link">$Title</a></h3>
    <p>$DisplayLocation - $DisplaySalary</p>
    <p>$Summary</p>
<% end_loop %>

<p><a href="$jobAlertSubscription.UnsubscribeLink">Unsubscribe</a> | <a href="$jobAlertSubscription.UpdateSubscriptionLink">Update subscription</a></p>