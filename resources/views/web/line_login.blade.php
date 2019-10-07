@extends('layouts.main')

@section('content')
<div class="main-title2">LINE 登入</div>
<div class="content">
  <button type="button" id="btn" onclick="callAndroid()">登入</button>
</div>
@endsection

@section('script')
<script>
function callAndroid(){
  // 由于对象映射，所以调用test对象等于调用Android映射的对象
   test.hello("喵~");
}
</script>
@endsection
