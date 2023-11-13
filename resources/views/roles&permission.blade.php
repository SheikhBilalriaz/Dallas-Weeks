@extends('partials/master')
@section('content')
<section class="blacklist team_management role_per_sec">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="filter_head_row d-flex">
                    <div class="cont">
                    <h3>Roles & permissions</h3>
                    </div>
                </div>
                <!-- <div class="filtr_desc">
                    <div class="d-flex">
                        <strong>Team members</strong>
                        <div class="filter">
                            <form action="/search" method="get" class="search-form">
								<input type="text" name="q" placeholder="Search...">
								<button type="submit">
								<i class="fa fa-search"></i>
								</button>
							</form>
                            <a href="javascript:;" class="roles_btn">Roles & permissions</a>
                        </div>
                    </div>
                </div> -->
                <div class="data_row">
                    <div class="data_head">

<table class="data_table w-100">
  <thead>
    <tr>
      <th width="70%">Permission</th>
      <th width="10%">Owner</th>
      <th width="10%">Admin</th>
      <th width="10%">Editor</th>
    </tr>
  </thead>
  <tbody>
  @for ($i = 0; $i < 10; $i++)
  <tr>
      <td class="per">Manage webhooks</td>
      <td><input type="checkbox" name="owner" class="check"></td>
      <td><input type="checkbox" name="admin" class="check"></td>
      <td><input type="checkbox" name="editor" class="check"></td>
    </tr>
@endfor
  </tbody>
</table>


                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection