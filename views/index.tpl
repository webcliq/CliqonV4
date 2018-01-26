@include('partials/header.tpl')
@include('partials/cookieconsent.tpl')
</head>
<body>
    @include('partials/nav.tpl')

    <div class="splash-container">
        <div class="splash">
            <h1 class="splash-head bold">@($cfg['site']['name'])</h1>
            <p class="splash-subhead">@($cfg['site']['description'])</p>
            <p><a href="/admindesktop/@($idiom)/dashboard/" class="pure-button pure-button-primary">@(Q::uStr('4:Let us get started'))</a></p>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="content">
            <h2 class="content-head is-center">@($cfg['site']['description'])</h2>

            <div class="pure-g">
                <div class="l-box pure-u-1 pure-u-md-1-2 pure-u-lg-1-4">

                    <h3 class="content-subhead">
                        <i class="fa fa-rocket"></i>
                        @(Q::uStr('5:Get Started Quickly'))
                    </h3>
                    <p>@(Q::uStr('6:Organise your template ...'))</p>

                </div>
                <div class="l-box pure-u-1 pure-u-md-1-2 pure-u-lg-1-4">

                    <h3 class="content-subhead">
                        <i class="fa fa-paste"></i>
                        @(Q::uStr('7:Copy it into views ...'))
                    </h3>
                    <p>@(Q::uStr('8:Publish your files'))</p>

                </div>
                <div class="l-box pure-u-1 pure-u-md-1-2 pure-u-lg-1-4">

                    <h3 class="content-subhead">
                        <i class="fa fa-paragraph"></i>
                        @(Q::uStr('9:Enter your content'))
                    </h3>
                    <p>@(Q::uStr('10:Use the Admin system'))</p>

                </div>
                <div class="l-box pure-u-1 pure-u-md-1-2 pure-u-lg-1-4">

                    <h3 class="content-subhead">
                        <i class="fa fa-external-link"></i>
                        @(Q::uStr('11:Publish your website'))
                    </h3>
                    <p>@(Q::uStr('12:Transfer your database'))</p>

                </div>
            </div>
        </div>

        <div class="ribbon l-box-lrg pure-g">
            <div class="l-box-lrg is-center pure-u-1 pure-u-md-1-2 pure-u-lg-2-5">
                <img width="300" alt="File Icons" class="pure-img-responsive" src="/views/img/file-icons.png">
            </div>
            <div class="pure-u-1 pure-u-md-1-2 pure-u-lg-3-5">
                <h2 class="content-head content-head-ribbon">@(Q::uStr('13:Creating your own bits and pieces'))</h2>
                <p>@(Q::uStr('14:We have loads and classes and methods'))</p>
            </div>
        </div>

        <div class="content">
            <h2 class="content-head is-center">@(Q::uStr('15:So what are you waiting for? Let us get on with it!'))</h2>

            <div class="pure-g">
                <div class="l-box-lrg pure-u-1 pure-u-md-2-5">
                    <h4>@(Q::uStr('17:Ask a quick question'))</h4>
                    <form method="POST" action="https://formspree.io/info@cliqon.com" name="sendmessage" id="sendmessage" class="pure-form pure-form-stacked">
                        <fieldset>

                            <label for="name">@(Q::uStr('18:Your Name'))</label>
                            <input id="name" name="name" type="text" placeholder="@(Q::uStr('18:Your Name'))" autofocus required>

                            <label for="email">@(Q::uStr('19:Your Email'))</label>
                            <input id="email" name="_replyto" type="email" placeholder="@(Q::uStr('19:Your Email'))" required>

                            <label for="subject">@(Q::uStr('20:Your Subject'))</label>
                            <input id="subject" name="_subject" type="text" placeholder="@(Q::uStr('20:Your Subject'))" required>

                            <label for="message">@(Q::uStr('21:Your Message'))</label>
                            <textarea name="message" id="message" style="width: 100%;" required>@(Q::uStr('21:Message'))</textarea>
                            <br />
                            <button type="submit" class="pure-button">@(Q::uStr('22:Send'))</button>
                        </fieldset>
                    </form>
                </div>

                <div class="l-box-lrg pure-u-1 pure-u-md-3-5">
                    <h4>@(Q::uStr('23:Get support'))</h4>
                    <p>@(Q::uStr('26:There is a link at the top of this page .... '))</p>

                    <h4>@(Q::uStr('24:So remember'))</h4>
                    <p>@(Q::uStr('25:No task is too large or too small for our adjustable spanner'))</p>
                </div>
            </div>

        </div>

    	@include('partials/footer.tpl')
    </div>

    @include('partials/script.tpl')

<!-- End of Page -->
@include('partials/end.tpl')