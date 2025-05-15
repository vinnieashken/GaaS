<nav class="sidebar sidebar-sticky">
    <div class="sidebar-content  js-simplebar">
        <a class="sidebar-brand" href="#">
            <i class="align-middle" data-feather="send"></i>
            <span class="align-middle">Paper</span>
        </a>
        <ul class="sidebar-nav">
            <li class="sidebar-header">
                Main
            </li>
            <li class="sidebar-item active">
                <a class="sidebar-link font-weight-bold" href="{{ url('/') }}">
                    <i class="align-middle" data-feather="home"></i> <span class="align-middle">Home</span>
                </a>
            </li>
            <li class="sidebar-item active">
                <a class="sidebar-link font-weight-bold" href="{{ route('apps') }}">
                    <i class="align-middle" data-feather="cpu"></i> <span class="align-middle">Apps</span>
                </a>
            </li>
            <li class="sidebar-item active">
                <a class="sidebar-link font-weight-bold" href="{{ route('transactions') }}">
                    <i class="align-middle" data-feather="credit-card"></i> <span class="align-middle">Transactions</span>
                </a>
            </li>
            <li class="sidebar-header">
                ADMIN
            </li>
            <li class="sidebar-item active">
                <a class="sidebar-link font-weight-bold" href="{{ route('users') }}">
                    <i class="align-middle" data-feather="users"></i> <span class="align-middle">Users</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#ui" data-toggle="collapse" class="font-weight-bold sidebar-link collapsed">
                    <i class="align-middle" data-feather="settings"></i> <span class="align-middle">settings</span>
                </a>
                <ul id="ui" class="sidebar-dropdown list-unstyled collapse ">
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('currencies') }}">
                            <i class="align-middle" data-feather="dollar-sign"></i>
                            Currencies
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('gateways') }}">
                            <i class="align-middle" data-feather="smartphone"></i>
                            Gateways
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
