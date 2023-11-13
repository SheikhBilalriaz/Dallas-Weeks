@extends('partials/master')
@section('content')
<section class="dashboard">
	<div class="container-fluid">
		<div class="row">
		@include('partials/dashboard_sidebar')
			<div class="col-lg-8">
				<div class="dashboard_cont">
					<div class="row_filter d-flex align-items-center justify-content-between">
						<div class="account d-flex align-items-center">
							<img src="assets/img/acc.png" alt=""><span>John dow</span>
						</div>
						<div class="form_add d-flex">
							<form action="/search" method="get" class="search-form">
								<input type="text" name="q" placeholder="Search...">
								<button type="submit">
								<i class="fa fa-search"></i>
								</button>
							</form>
							<div class="add_btn">
								<a href="#"  class="" data-toggle="modal" data-target="#addaccount"><i class="fa-solid fa-plus"></i></a>Add account
							</div>
						</div>
					</div>
					<hr>
					<div class="row_table">
						<div class="add_account_div">
							<img src="assets/img/small_img.png" alt="">
						<p class="text-center" >You don't hanve any account yet. Start by adding your first account.</p>
						<div class="add_btn">
							<a href="#" data-toggle="modal" data-target="#addaccount"><i class="fa-solid fa-plus"></i>
						</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- basic modal -->
<div class="modal fade step_form_popup" id="addaccount" tabindex="-1" role="dialog" aria-labelledby="addaccount" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="text-center">Add Account</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
        </button>
      </div>
      <div class="modal-body text-center">
        
<!-- Add Account Popup -->
<form action="data.php" method="post" class="form step_form">
        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress" id="progress"></div>
            <div class="progress-step active" data-title="Add account"></div>
            <div class="progress-step" data-title="Company "></div>
            <div class="progress-step" data-title="Payment"></div>
            <div class="progress-step" data-title="Account info"></div>
			<div class="progress-step" data-title="Integration"></div>
        </div>

        <!-- Steps -->
        <div class="form-step active">
            <h3>Personal Informations</h3>
            <div class="form_row row">
			<div class="input-group col-12">
                <label for="address">Street address</label>
                <input type="text" name="address" id="address" placeholder="Enter your street address">
            </div>

            <div class="input-group col-6">
                <label for="City">City</label>
                <input type="text" name="City" id="City" placeholder="Enter your city">
            </div>
            <div class="input-group col-6" >
                <label for="State">State</label>
                <input type="text" name="State" id="State" placeholder="Enter your state">
            </div>
			<div class="input-group col-12" >
                <label for="Company">Company name</label>
                <input type="text" name="Company" id="Company" placeholder="Enter your company name">
            </div>
			</div>
            <div class="btn-group">
                <a class="btn btn-next">Next</a>
            </div>
        </div>
        <div class="form-step ">
            <h3>Contact Informations</h3>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email">
            </div>
            <div class="input-group">
                <label for="phone">Phone Number</label>
                <input type="phone" name="phone" id="phone">
            </div>
            <div class="input-group">
                <label for="summary">Profile Summary</label>
                <textarea name="summary" id="summary" cols="42" rows="6"></textarea>
            </div>
            <div class="btn-group">
                <a class="btn btn-prev">Previous</a>
                <a class="btn btn-next">Next</a>
            </div>
        </div>
        <div class="form-step ">
            <h3>Experiences</h3>
            <div class="experiences-group">
                <div class="experience-item">
                    <div class="input-group">
                        <label for="title">Company name</label>
                        <input type="text" name="job-title[]" id="job-title">
                    </div>
                    <div class="input-group">
                        <label for="start-date">Start date</label>
                        <input type="date" name="start-date[]" id="start-date">
                    </div>
                    <div class="input-group">
                        <label for="end-date">End date</label>
                        <input type="date" name="end-date[]" id="end-date">
                    </div>
                    <div class="input-group">
                        <label for="job-description">Description</label>
                        <textarea name="job-description[]" id="job-description" cols="42" rows="6"></textarea>
                    </div>
                </div>
            </div>
            <div class="add-experience">
                <a class="add-exp-btn"> + Add Experience</a>
            </div>
            <div class="btn-group">
                <a class="btn btn-prev">Previous</a>
                <a class="btn btn-next">Next</a>
            </div>
        </div>
        <div class="form-step ">
            <h3>Social Links</h3>
            <div class="input-group">
                <label for="linkedin">LinkedIn</label>
                <div class="input-box">
                    <span class="prefix">linkedin.com/in/</span>
                    <input id="linkedin" name="linkedin" type="text" placeholder="USER123" />
                </div>
            </div>
            <div class="input-group">
                <label for="twitter">Twitter</label>
                <div class="input-box">
                    <span class="prefix">twitter.com/</span>
                    <input id="twitter" name="twitter" type="text" placeholder="USER123" />
                </div>
            </div>
            <div class="input-group">
                <label for="github">Github</label>
                <div class="input-box">
                    <span class="prefix">github.com/</span>
                    <input id="github" name="facebook" type="text" placeholder="USER123" />
                </div>
            </div>
            <div class="btn-group">
                <a class="btn btn-prev">Previous</a>
                <input type="submit" value="Complete" name="complete" class="btn btn-complete">
            </div>
        </div>
    </form>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
    </div>
  </div>
</div>

@endsection