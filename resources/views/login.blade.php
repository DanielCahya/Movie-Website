<x-layout>

  <div class="wrapper">
    <h1>Login</h1>
    @if (($errors->any()))
    <div class="alert-alertdanger">
      <ul>
        @foreach ($errors->all() as $item)
            <li>{{ $item }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    <form action="" method="post">
    @csrf
      <div class="input-box">
        <input type="text" name="username" id="uname"placeholder="Username" value="{{old('text')}}" required>
        <i class='bx bxs-user'></i>
      </div>
      <div class="input-box">
        <input type="password" name="password" id="password"placeholder="Password" required>
        <i class='bx bxs-lock-alt' ></i>
      </div>
      <button type="submit" name="submit" class="btn">Login</button>
      <div class="register-link">
        <p>Don't have an account? <a href="{{url('register')}}">Register</a></p>
      </div>
    </form>
  </div>
</x-layout>