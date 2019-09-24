<% with $JobAd %>
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-4 col-xl-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <th scope="row">Title</th>
                            <td>$Title</td>
                        </tr>
                        <tr>
                            <th scope="row">Reference</th>
                            <td>$JobAdderReference</td>
                        </tr>
                        <tr>
                            <th scope="row">Location</th>
                            <td>$DisplayLocation</td>
                        </tr>
                        <tr>
                            <th scope="row">Work Type</th>
                            <td>$WorkType.Title</td>
                        </tr>
                        <tr>
                            <th scope="row">Salary</th>
                            <td>$DisplaySalary</td>
                        </tr>
                        <tr>
                            <th scope="row">Expires</th>
                            <td>$ExpiresAt.Format('dd MMMM yyyy')</td>
                        </tr>
                        <tr>
                            <th scope="row">Category</th>
                            <td>$CategoryString</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-12 col-lg-8 col-xl-9">
                <h1>$Title</h1>
                $Description
                <a href="$ApplicationLink" target="_blank" class="mt-4 btn btn-primary btn-block">Apply</a>
            </div>
        </div>
    </div>
<% end_with %>