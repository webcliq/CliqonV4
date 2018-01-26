@include('partials/admheader.tpl')
</head>
<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden footer-fixed">

    @include('partials/admnav.tpl')

    <!-- Main content -->
    <main class="main" id="admincontent">
        @raw($admincontent)
    </main>

    @include('partials/admfooter.tpl')

@include('partials/admscript.tpl')
