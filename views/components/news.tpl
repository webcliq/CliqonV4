<section class="content-faded">
  <div class="py-5">
    <div class="container" id="datalist">

      <!-- No rows returned -->
      <div class="row" v-if="rows.length < 1">
        <div class="col-md-4">
          <h3>@(Q::cStr('144:No records available'))</h3>
        </div>
      </div>

      <!-- Rows are returned -->
      <div class="row" v-else v-for="(row, rowid) in rows" v-bind:data-id="row.id" >
        <div class="col-md-4">
          <img class="img-fluid d-block mb-4 w-100 img-thumbnail" v-bind:src="row.d_image"> 
          <h5 class="bluec">@(Q::uStr('110:Category')): <span class="bold bluec">{{row.c_category}}</span></h5>
          <h5 class="bluec">@(Q::uStr('111:Date')): <span class="bold">{{row.d_date}}</span></h5>
          <h5 class="bluec">@(Q::uStr('109:Author')): <span class="bold">{{row.d_author}}</span></h5>
        </div>
        <div class="col-md-8">
          <h2 class="text-primary pt-3">{{row.d_title}}</h2>
          <div class="more" v-html="row.d_text"></div>
        </div>
      </div>

    </div>
  </div>
</section>
