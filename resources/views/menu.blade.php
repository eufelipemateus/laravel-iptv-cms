@foreach($menusList as $menu)

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        {{ __($menu['title']) }}
    </div>

    @foreach($menu['menus'] as $item )
        <!-- Nav Item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route($item['route']) }}">
                <i class="fas fa-fw fa-{{ $item['icon'] }}"></i>
                <span>{{ __($item['name']) }}</span>
            </a>
        </li>
    @endforeach
    <!-- Divider
    <hr class="sidebar-divider"> -->
@endforeach
