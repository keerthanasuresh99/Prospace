<div class="sidebar-content js-simplebar">
    <a class="sidebar-brand" href="index.html">
        <span class="d-flex justify-content-center">
            <img src="{{ asset('storage/logo/sidebar_logo.png') }}" alt="homepage" style="max-width: 120px;" />
        </span>
    </a>
    @php
        $view_menu = config('admin.menu');
    @endphp
    <ul class="sidebar-nav">
        <li class="sidebar-header">

        </li>

        {{-- <li class="sidebar-item active">
            <a class="sidebar-link" href="index.html">
                <i class="align-middle" data-feather="globe"></i> <span class="align-middle">Dashboard</span>
            </a>
        </li> --}}
        <li class="sidebar-item">
            <a class="sidebar-link" href="">
                <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Base Settings</span>
                <ul>
                    @if (Auth::check())
                        <li class="sidebar-item {{ request()->is('base-settings/list-achievers') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('list-achievers') }}">
                                <i class="align-middle"></i> <span class="align-middle">Add Achiever
                                    List</span>
                            </a>
                        </li>
                        <li
                            class="sidebar-item {{ request()->is('base-settings/list-sub-achievers') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('list-sub-achievers') }}">
                                <i class="align-middle"></i> <span class="align-middle">Add Sub Achiever List</span>
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('base-settings/list-templates') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('list-templates') }}">
                                <i class="align-middle"></i> <span class="align-middle">Add
                                    Template</span>
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('base-settings/list-templates') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('list-event-builders') }}">
                                <i class="align-middle"></i> <span class="align-middle">Event Builders</span>
                            </a>
                        </li>
                    @endif
                </ul>
        </li>
    </ul>
    </a>
    </li>
    </ul>
</div>
