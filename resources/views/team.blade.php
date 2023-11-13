@extends('partials/master')
@section('content')
<section class="blacklist team_management">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="filter_head_row d-flex">
                    <div class="cont">
                    <h3>Team Management</h3>
                    <p>Invite team members and manage team permissions.</p>
                    </div>
                    
                    <div class="filt_opt d-flex">
                    <div class="add_btn ">
								<a href="javascript:;" class="" data-toggle="modal" data-target="#"><i class="fa-solid fa-plus"></i></a>Add team member
							</div>
                        <select name="num" id="num">
                            <option value="01">10</option>
                            <option value="02">20</option>
                            <option value="03">30</option>
                            <option value="04">40</option>
                        </select>
                    </div>
                </div>
                <div class="filtr_desc">
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
                </div>
                <div class="data_row">
                    <div class="data_head">

<table class="data_table w-100">
  <thead>
    <tr>
      <th width="75%">Name</th>
      <th width="10%">Email</th>
      <th width="5%">Role</th>
      <th width="5%">Status</th>
      <th width="5%">Action</th>
    </tr>
  </thead>
  <tbody>
  @for ($i = 0; $i < 5; $i++)
  <tr>
      <td><div class="d-flex align-items-center"><img src="assets/img/acc.png" alt=""><strong>John doe</strong></div></td>
      <td>info@johndoe.com</td>
      <td>Executive</td>

      <td><a href="javascript:;" class="black_list_activate">Active</a></td>
      <td>
        <a href="javascript:;" type="button" class="setting setting_btn" id=""><i class="fa-solid fa-gear"></i></a>
        <ul class="setting_list">
            <li><a href="javascript:;">Edit</a></li>
            <li><a href="javascript:;">Delete</a></li>
        </ul>   
    </td>
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