<div class="container">
    <div class="row">

        <div class="col-12 col-lg-4">

            <form name="job-search" method="get">

                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" class="form-control" id="search" name="s" value="$Search">
                </div>

                <a href="javascript:void(0);" class="btn btn-primary btn-block" data-toggle="collapse"
                   data-target="#job-search-countries-collapse" aria-expanded="false"
                   aria-controls="job-search-countries-collapse">
                    Countries <i class="fal fa-plus"></i>
                </a>
                <div class="collapse <% if $IsSelectedLocations %>show <% end_if %>" id="job-search-countries-collapse">
                    <% loop $Countries %>
                        <a href="javascript:void(0);" class="btn btn-secondary btn-block" data-toggle="collapse"
                           data-target="#job-search-country-collapse-$ID" aria-expanded="false"
                           aria-controls="job-search-country-collapse-$ID">
                            $Title <i class="fal fa-plus"></i>
                        </a>
                        <div class="collapse py-2 <% if $Top.IsSelectedLocationsInCountry($ID) %>show <% end_if %>" id="job-search-country-collapse-$ID">
                            <% loop $Locations %>
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        id="location$ID"
                                        name="$Top.LocationParam[]"
                                        value="$ID"
                                        <% if $Top.IsSelectedLocation($ID) %>checked<% end_if %>
                                    />
                                    <label class="form-check-label" for="location$ID">$Title</label>
                                </div>
                            <% end_loop %>
                        </div>
                    <% end_loop %>
                </div>

                <a href="javascript:void(0);" class="btn btn-primary btn-block" data-toggle="collapse"
                   data-target="#job-search-categories-collapse" aria-expanded="false"
                   aria-controls="job-search-categories-collapse">
                    Categories <i class="fal fa-plus"></i>
                </a>
                <div class="collapse <% if $IsSelectedSubCategories %>show <% end_if %>" id="job-search-categories-collapse">
                    <% loop $Categories %>
                        <a href="javascript:void(0);" class="btn btn-secondary btn-block" data-toggle="collapse"
                           data-target="#job-search-category-collapse-$ID" aria-expanded="false"
                           aria-controls="job-search-category-collapse-$ID">
                            $Title <i class="fal fa-plus"></i>
                        </a>
                        <div class="collapse py-2 <% if $Top.IsSelectedSubCategoriesInCategory($ID) %>show <% end_if %>" id="job-search-category-collapse-$ID">
                            <% loop $SubCategories %>
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        id="subcategory$ID"
                                        name="$Top.SubCategoryParam[]"
                                        value="$ID"
                                        <% if $Top.IsSelectedSubCategory($ID) %>checked<% end_if %>
                                    />
                                    <label class="form-check-label" for="subcategory$ID">$Title</label>
                                </div>
                            <% end_loop %>
                        </div>
                    <% end_loop %>
                </div>

                <a href="javascript:void(0);" class="btn btn-primary btn-block" data-toggle="collapse"
                   data-target="#job-search-work-type-collapse" aria-expanded="false"
                   aria-controls="job-search-work-type-collapse">
                    Work type <i class="fal fa-plus"></i>
                </a>
                <div class="collapse py-2 <% if $IsSelectedWorkTypes %>show <% end_if %>" id="job-search-work-type-collapse">
                    <% loop $WorkTypes %>
                        <div class="form-check">
                            <input
                                type="checkbox"
                                id="worktype$ID"
                                name="$Top.WorkTypeParam[]"
                                value="$ID"
                                <% if $Top.IsSelectedWorkType($ID) %>checked<% end_if %>
                            />
                            <label class="form-check-label" for="worktype$ID">$Title</label>
                        </div>
                    <% end_loop %>
                </div>

                <a href="javascript:void(0);" class="btn btn-primary btn-block" data-toggle="collapse"
                   data-target="#job-search-salary-collapse" aria-expanded="false"
                   aria-controls="job-search-salary-collapse">
                    Salary <i class="fal fa-plus"></i>
                </a>
                <div class="collapse py-2 <% if $IsSelectedSalaryFields %>show<% end_if %>" id="job-search-salary-collapse">
                    <div class="form-group">
                        <label for="salary-minimum">Minimum</label>
                        <input type="number" min="0" class="form-control" id="salary-minimum" name="smin" value="$SalaryMinimum">
                    </div>
                    <div class="form-group">
                        <label for="salary-maximum">Maximum</label>
                        <input type="number" min="0" class="form-control" id="salary-maximum" name="smax" value="$SalaryMaximum">
                    </div>
                    <div class="form-group">
                        <label for="salary-frequency-dropdown">Frequency</label>
                        <select class="form-control" id="salary-frequency-dropdown" name="$Top.SalaryFrequencyParam">
                            <option>Any</option>
                            <% loop $SalaryFrequencies %>
                                <option value="$ID" <% if $Top.IsSelectedSalaryFrequency($ID) %>selected="selected"<% end_if %>>$Title</option>
                            <% end_loop %>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="currency-dropdown">Currency</label>
                        <select class="form-control" id="currency-dropdown" name="$Top.CurrencyParam">
                            <option>All</option>
                            <% loop $Currencies %>
                                <option value="$ID" <% if $Top.IsSelectedCurrency($ID) %>selected="selected"<% end_if %>>$Title</option>
                            <% end_loop %>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-block btn-primary">Search <i class="fal fa-search"></i> </button>
                <a href="$Link" class="btn btn-block btn-secondary">Reset</a>

            </form>

        </div>

        <div class="col-12 col-lg-8">
            <div class="row">
                <% loop $Results %>
                    <div class="col-12">
                        <h3>$Title</h3>
                        <p>Salary: $DisplaySalary</p>
                        <p>Location: $LocationString</p>
                        <p>Category: $CategoryString</p>
                        <p>Work type: $WorkType.Title</p>
                        <p>$Summary</p>
                        <a href="$Link" class="btn btn-primary">View</a>
                        <a href="$ApplicationLink" target="_blank" class="btn btn-secondary">Apply</a>
                    </div>
                <% end_loop %>
                <div class="col-12">
                    <nav class="pagination-nav">
                        <% if $Results.MoreThanOnePage %>
                            <ul class="pagination justify-content-center">
                                <% if $Results.NotFirstPage %>
                                    <li class="page-item"><a class="page-link" href="$Results.PrevLink">&lt;</a></li>
                                <% else %>
                                    <li class="page-item disabled"><a class="page-link" href="#">&lt;</a></li>
                                <% end_if %>
                                <% loop $Results.PaginationSummary(4) %>
                                    <% if $CurrentBool %>
                                        <li class="page-item active"><a class="page-link" href="#">$PageNum</a></li>
                                    <% else %>
                                        <% if $Link %>
                                            <li class="page-item"><a class="page-link" href="$Link">$PageNum</a></li>
                                        <% else %>
                                            <li class="page-item disabled"><a class="page-link" href="javascript:void(0)">...</a></li>
                                        <% end_if %>
                                    <% end_if %>
                                <% end_loop %>
                                <% if $Results.NotLastPage %>
                                    <li class="page-item"><a class="page-link" href="$Results.NextLink">&gt;</a></li>
                                <% else %>
                                    <li class="page-item disabled"><a class="page-link" href="#">&gt;</a></li>
                                <% end_if %>
                            </ul>
                        <% end_if %>
                        <strong>$Results.FirstItem - $Results.LastItem</strong> of <strong>$Results.TotalItems</strong> results
                    </nav>
                </div>
            </div>
        </div>

    </div>
</div>