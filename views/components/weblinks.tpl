<section class="content-faded">
  <div class="py-5">
    <div class="container">
      <div class="row" id="datalist" role="template">
        <div class="col-4">
          <h3>@(Q::uStr('112:Search and filter'))</h3>

          <div class="input-group">
              <input type="text" class="form-control" placeholder="@(Q::cStr('9999:Search for')) ..." aria-label="@(Q::cStr('239:Search for')) ...">
              <span class="input-group-btn">
                <button class="btn btn-secondary" type="button"><i class="fa fa-search"></i></button>
                <button class="btn btn-secondary" type="button"><i class="fa fa-refresh"></i></button>
              </span>                         
          </div>

        </div>

        <div class="col-8">
          <h3 class="bold">@(Q::uStr('107:Useful links'))</h3>
          <table class="table table-striped table-hover">

              <!-- No rows returned -->
              <tr v-if="rows.length < 1">
                <td></td>
                  <td colspan=3 class="pad bold mt10">@(Q::cStr('144:No records available'))</td>
                  <td></td>
              </tr>

              <!-- Rows are returned -->
              <tr v-else v-for="(row, rowid) in rows" v-bind:data-id="row.id">
                <td 
                  v-for="(col, fld) in cols" 
                  v-html="row[fld]" 
                  v-if="col.class != 'undefined'" v-bind:class="col.class"  
                  v-if="col.params != 'undefined'" v-bind:data-params="col.params"    
                  v-if="col.action != 'undefined'" v-on:click="rowbutton($event, row)" v-bind:data-action="col.action" 
                ></td>
              </tr>

          </table>

          <nav id="pager" class="navbar navbar-light bg-light justify-content-between bluec"></nav>

        </div> <!-- End col  -->
      </div>
    </div>
  </div>
</section>