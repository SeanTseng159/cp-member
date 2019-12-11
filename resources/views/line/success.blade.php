@extends('layouts.main')

@section('content')
<div class="content" style="margin-top: 10vh;">
  <div class="main-title2">
    <img src="../../img/logo_mobile.png" style="max-width:100%;"></img>
  </div>
  <div class="main-title2" style="margin-top:50px;">
    Line 登入成功!!
  </div>
  <!-- <div class="main-title2">
    <img src="img/btn_login_base.png" style="max-width:100%;cursor: pointer;" @click="callAndroid()"></img>
  </div> -->
</div>
@endsection

@section('script')
<script>
var backend_data = {!! $data !!};
var app = new Vue({
    el: '#app',
    data() {
        return {

        }
    },
    mounted() {
      console.log(backend_data);
    },
    methods: {
      callAndroid(){
        // 由于对象映射，所以调用test对象等于调用Android映射的对象
        console.log("喵");
         // test.hello("喵~");
      }
    }
});
</script>
@endsection
