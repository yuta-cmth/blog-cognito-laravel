@if (empty($user))
  <div>
    Hello, guest. Please login
  </div>
  <a href="{{ route('login') }}">Login</a>
@else
  <div>
    Hello, {{ $user['username'] }}
  </div>
  <a href="{{ route('logout') }}">Logout</a>
@endif
