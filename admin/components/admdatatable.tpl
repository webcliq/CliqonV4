<!-- Admin Datatables Component Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>
	
	<li class="breadcrumb-item capitalize">@(Q::cStr('238:Table'))</li>
	<li class="breadcrumb-item capitalize">@($table)</li>
	<li class="breadcrumb-item capitalize active">@($tabletype)</li>

</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row" id="">
				
			<div class="col-12">			
				<div class="card">
					<div class="card-block" id="@($tblopts.tableId)" style="@($tblopts.style)">
						
						<!-- Toolbar -->
						<div class="toolbar" id="toolbar"></div>

						<!-- DataTable -->
	                    <table class="@($tblopts.classes)" id="cliqontable">

	                    	<!-- Search row -->
	                        @if($tblopts.tableSearch == 'true')
	                        <thead>
	                            <tr class="">
	                                <th scope="col"></th>
	                                <th scope="col" v-for="(col, colid) in cols">
	                                	<div v-if="col.searchable == true" class="input-group input-group-sm col">
      										<input type="text" class="form-control"  placeholder="@(Q::cStr('149:Search for')) ..." aria-label="@(Q::cStr('239:Search for')) ..." v-bind:data-name="colid">
      										<span class="input-group-btn">
        										<button class="btn btn-secondary" v-on:click="searchbutton($event)" type="button" v-bind:data-id="colid"><i class="fa fa-search"></i></button>
        										<button class="btn btn-secondary" v-on:click="clearbutton($event)" type="button" v-bind:data-id="colid"><i class="fa fa-refresh"></i></button>
      										</span>      										
    									</div>
	                                </th>
	                                <th scope="col"></th>
	                            </tr>
	                        </thead>
	                        @endif

	                        <!-- Column header row -->
	                        <thead>
	                            <tr class="lightgray">
	                            	<th>[X]</th>
	                                <th v-for="(col, colid) in cols">
	                                	{{col.title}}
	                                	<!--
											sort-alpha-asc, sort-alpha-desc sort-up, sort-down
	                                	-->
	                                	<i v-if="col.sortable == true" class="ml5 fa fa-sort" v-bind:data-id="colid" v-on:click="sortbutton($event)" ></i>
	                                </th>
	                                <th>*</th>
	                            </tr>
	                        </thead>

	                        <!-- Body of table -->
	                        <tbody>
	                            
	                            <!-- No rows returned -->
	                            <tr v-if="rows.length < 1">
	                            	<td></td>
	                                <td colspan=3 class="pad bold mt10">@(Q::cStr('144:No records available'))</td>
	                                <td></td>
	                            </tr>

	                            <!-- Rows are returned -->
	                            <tr v-else v-for="(row, rowid) in rows" v-bind:data-id="row.id">
	                            	<td scope="row"><input type="checkbox" class="form-control mb0 mt5" v-bind:data-id="row.id" v-bind:data-uname="row.c_username" v-bind:data-email="row.c_email" ></td>
	                                <td 
	                                	v-for="(col, colid) in cols" 
	                                	v-html="row[colid]"
	                                	v-bind:data-id="colid"  
	                                	v-if="col.class != 'undefined'" v-bind:class="col.class"  
	                                	v-if="col.params != 'undefined'" v-bind:data-params="col.params"  	
	                                	v-if="col.action != 'undefined'" v-on:click="rowbutton($event, row)" v-bind:data-action="col.action" 
	                                ></td>
	                                <td class="nowrap" align="right">
	                                	<i 
	                                		v-for="(icn, action) in rowicons" 
	                                		v-bind:class="'fa fa-fw bluec pointer fa-'+icn.icon" 
	                                		v-bind:data-action="action" 
	                                		v-on:click="rowbutton($event, row)" 
	                                		v-bind:title="icn.title" 
	                                		v-bind:data-formid="icn.formid"
	                                	></i>
	                                </td>
	                            </tr>

	                        </tbody> <!-- End table body -->
	                    </table> <!--End table -->

						<!-- Pagination -->
						<div class="card-text mt10">

	                    	<span class="left mt5" style="vertical-align: bottom;" id="paginationtext">
	                    		<span class="ucfirst">{{records.recordstxt}}</span>
	                    		&nbsp;{{records.start}}
	                    		&nbsp;{{records.fromtxt}}
	                    		&nbsp;{{records.end}}
	                    		&nbsp;{{records.totxt}}
	                    		&nbsp;{{records.total}}
	                    	</span>

	                    	<span>
	                    		<select class="form-control form-control-sm left ml10" name="pageselect" id="pageselect" style="width: 60px;">
	                    			<option disabled value="">@(Q::cStr('108:Select'))</option>
	                    			<option class="" v-for="opt in pagerselect" v-bind:value="opt.value" :selected="selected == opt.value">{{opt.text}}</option>
	                    		</select>
	                    	</span>

		                    <nav class="right">
		                        <ul class="pagination smaller" id="tablepagination"></ul>
		                    </nav>
		                </div>
	                    
					</div>	
				</div>
			</div>
							
		</div> <!-- End datatable row -->
	</div>
</div>

<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>


