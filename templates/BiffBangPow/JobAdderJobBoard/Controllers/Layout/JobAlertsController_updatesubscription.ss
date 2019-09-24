<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Update job alerts subscription</h1>
        </div>
        <% if $Message %>
            <div class="col-12">
                <p>$Message</p>
            </div>
        <% end_if %>
        <% if $UpdateSubscriptionForm %>
            <div class="col-12">
                $UpdateSubscriptionForm
            </div>
        <% end_if %>
    </div>
</div>