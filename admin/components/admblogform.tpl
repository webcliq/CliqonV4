<!-- Admin Blog Form Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>
	
	<li class="breadcrumb-item capitalize">@(Q::cStr('9999:Blog'))</li>
	<li class="breadcrumb-item capitalize active">@($action)</li>

</ol>

<div class="container-fluid">
	<div class="card col-2 right">	
		<div class="card-block">

			<!-- Tabs -->			
			<div class="nav nav-pills" id="pills_tab" role="tablist" aria-orientation="vertical">	
				<a class="nav-link" id="pill_common" href="#tab_common" role="tab" aria-controls="pill_common">Common</a>
				@foreach($idioms as $lcdcode => $lcdname)
				<a class="nav-link" id="pill_@($lcdcode)" href="#tab_@($lcdcode)" role="tab" aria-controls="pill_@($lcdcode)">@($lcdname)</a>
				@endforeach
				<a class="nav-link" id="pill_result" href="#tab_result" role="tab" aria-controls="pill_result">Result</a>
			</div>

			<hr class="style1" />

			<button type="button" id="submitbutton" class="btn btn-primary">@(Q::cStr('105:Submit'))</button>
		</div>
	</div>

	<!--
	[form:pageform:fields]
		; v-model = default
		c_type = 'blog'
		d_title = ''
		d_description = ''
		d_text = ''
	-->
	<div class="card card-outline-primary col-10 left">	
		<div class="card-block">
			
			<!-- Content  -->
			<div class="tab-content" id="pills_content">
				<form class="form" id="inputform" method="post" enctype="multipart/form-data">
					
					<input type="hidden" v-model="id" />
					<input type="hidden" v-model="c_type" />
					<input type="hidden" v-model="c_common" />

					<!-- Visible fields -->
					<div class="tab-pane hide" id="tab_common" role="tabpanel" aria-labelledby="pill_common">
						
						<!-- c_reference -->
						<div class="form-group">
							<label for="c_reference" class="required">@(Q::cStr('5:Reference'))</label>
							<input type="text" class="form-control col-2 nextref" id="c_reference" v-model="c_reference" required>
						</div>

						<!-- c_options -->
						<div class="form-group">
							<label for="c_options">@(Q::cStr('574:Tags'))</label>
							<span class="softred">
							<input type="text" class="form-control tagit" id="c_options" v-model="c_options">
							</span>
							<small class="form-text text-muted">@(Q::cStr('462:Tags .....'))</small>
						</div>

						<!-- Row -->

							<!-- c_status -->
							<div class="form-group col-6 left ml-15">
								<label for="c_status">@(Q::cStr('199:Status'))</label>
								<select class="form-control" id="c_status" v-model="c_status">
									@foreach(@($status) as $value => $label)
										<option value="@($value)">@($label)</option>
									@endforeach
								</select>		
							</div>

							<!-- c_group -->
							<div class="form-group col-6 right">
								<label for="c_category">@(Q::cStr('196:Category'))</label>
								<select class="form-control" id="c_category" v-model="c_category">
									@foreach(@($group) as $value => $label)
										<option value="@($value)">@($label)</option>
									@endforeach
								</select>		
							</div>
						<!-- Row ends  -->


						<!-- Row -->
							<!-- d_date -->
							<div class="form-group col-6 left ml-15">
								<label for="d_date">@(Q::cStr('183:Date'))</label>
								<input type="date" class="form-control datepicker" id="d_date" v-model="d_date">	
							</div>

							<!-- d_author -->
							<div class="form-group col-6 right">
								<label for="d_author">@(Q::cStr('420:Author'))</label>
								<select class="form-control" id="d_author" v-model="d_author">
									@foreach(@($author) as $value => $label)
										<option value="@($value)">@($label)</option>
									@endforeach
								</select>	
							</div>
						<!-- Row ends  -->

						<!-- d_image -->
						<div class="form-group">
							<label for="d_image">@(Q::cStr('217:Image'))</label>

							<div v-if="!d_image">
								<img src="/admin/img/blank.gif" class="h120 border" />
								<input type="file" class="form-control-file btn btn-sm btn-primary text-right top5" data-fldid="d_image" v-on:change="onFileChange" />
							</div>

							<div v-else >
								<img :src="d_image" class="h120 border" />
								<button type="button" class="btn btn-sm btn-danger text-right top5" v-on:click="removeImage" data-fldid="d_image" >@(Q::cStr('515:Remove image'))</button>
							</div>

						</div>

						<!-- c_notes -->
						<div class="form-group">
							<label for="c_notes">@(Q::cStr('8:Notes'))</label>
							<textarea class="form-control" id="c_notes" v-model="c_notes"></textarea>
						</div>

					</div>

					@foreach($idioms as $lcdcode => $lcdname)
					<!-- @($lcdname) -->
					<div class="tab-pane hide" id="tab_@($lcdcode)" role="tabpanel" aria-labelledby="pill_@($lcdcode)">

						<!-- d_title -->
						<div class="form-group">
							<label for="d_title_@($lcdcode)">@(Q::cStr('130:Title'))</label>
							<input type="text" class="form-control" id="d_title_@($lcdcode)" v-model="d_title.@($lcdcode)" required>
						</div>

						<!-- d_description -->
						<div class="form-group">
							<label for="d_description_@($lcdcode)">@(Q::cStr('456:Summary'))</label>
							<textarea class="form-control" id="d_description_@($lcdcode)" v-model="d_description.@($lcdcode)" rows=1 required></textarea>
						</div>

						<!-- d_text -->
						<div class="form-group">
							<label for="d_text_@($lcdcode)">@(Q::cStr('7:Content'))</label>
							<textarea class="form-control tiny" id="d_text_@($lcdcode)" v-model="d_text.@($lcdcode)"></textarea>
						</div>

					</div>	
					@endforeach		

					<!-- @($lcdname) -->
					<div class="tab-pane hide" id="tab_result" role="tabpanel" aria-labelledby="pill_result">
						{{$data}}
					</div>			
				</form>
			</div>	

		</div>				
	</div>
					
</div>

<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>


