@if ($item["submenu"]==[])
<li class="nav-item has-treeview" >
    <a href="{{url($item['url'])}}" class="nav-link">
      <i class="nav-icon {{$item["icono"]}}"></i>
      <p>
        {{$item["nombre"]}}
      </p>
    </a>
@else
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon {{$item["icono"]}}"></i>
            <p>
             {{$item["nombre"]}}
            </p>
            <i class="right fas fa-angle-left"></i>
        </a>
        <ul class="nav nav-treeview">
            @foreach ($item["submenu"] as $submenu)
            @include("theme.$theme.menu-item",["item" => $submenu])
            @endforeach
        </ul>   
    </li>    
@endif