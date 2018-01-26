<!-- Admin Report Designer -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="right">@raw($topbuttons)</li>
	<li class="breadcrumb-item capitalize">@(Q::cStr('61:Reports'))</li>
	<li class="breadcrumb-item capitalize active">@(Q::cStr('62:Report Designer'))</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-12">
				<div class="card col-12 minh36 pad" id="reportdesigner">
					
					<ul class="nav nav-tabs" id="repdestabs" role="tablist">
						<li class="nav-item" v-for="(prop, id) in toptabs">
							<a class="nav-link" data-toggle="tab" v-bind:href="id" v-bind:data-tabid="id" role="tab" v-bind:aria-controls="id">{{prop.label}}</a>
						</li>
					</ul>

					<div class="tab-content">
						<div v-for="(secn, cid) in sections" class="tab-pane minh30" v-bind:id="cid" role="tabpanel">
							<h5>{{secn.title}}</h5>
							<div v-html="secn.content"></div>
						</div>
					</div>

					<div class="mt20">
						<button v-for="(btn, bid) in buttons" v-on:click="clickbutton" type="button" v-bind:class="'mr5 btn btn-sm btn-'+btn.class" v-bind:id="bid" >{{btn.label}}</button>
						
					</div>

					<div class="hide" id="previewdialog">
						{{$data.formdef}}
					</div>
				</div>
			</div>		
		</div>
	</div>
</div>
