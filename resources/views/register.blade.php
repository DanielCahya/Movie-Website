<x-layout>

    <div class="wrapper">
      <h1>Register</h1>
      @if (($errors->any()))
      <div class="alert-alertdanger">
        <ul>
          @foreach ($errors->all() as $item)
              <li>{{ $item }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <form action="{{url('/registerproses')}}" method="post">
      @csrf
        <div class="input-box">
            <input type="text" name="email" id="email"placeholder="Email" required>
            <i class='bx bxs-user'></i>
        </div>
        <div class="input-box">
          <input type="text" name="username" id="username"placeholder="Username" value="{{old('text')}}" required>
          <i class='bx bxs-user'></i>
        </div>
        <div class="input-box">
          <input type="password" name="password" id="password"placeholder="Password" required>
          <i class='bx bxs-lock-alt' ></i>
        </div>
        <button type="submit" name="submit" class="btn">Register</button>
      </form>
    </div>
  </x-layout>