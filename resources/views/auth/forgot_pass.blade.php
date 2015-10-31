<!DOCTYPE html>
<html class="bg-black">
    <head>
        <meta charset="UTF-8">
        <title>Doorchat | Author</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
		
		<link rel="icon" type="image/png" href="/images/favicon.png" />
		
  @section('content')

<div class="container-fluid">
<div class="row">
 <div class="col-md-8 col-md-offset-2">
 <div class="panel panel-default">
 <div class="panel-heading">Reset Password</div>
 <div class="panel-body">

 @if (count($errors) > 0)
    <div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
        @endforeach
         </ul>
        </div>
@endif

<form class="form-horizontal" role="form" method="POST" action="/password/reset">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="token" value="{{ $token }}">

<div class="form-group">
<label class="col-md-4 control-label">E-Mail Address</label>
<div class="col-md-6">
<input type="email" class="form-control" name="email" value="{{ old('email') }}">
</div>
</div>

<div class="form-group">
<label class="col-md-4 control-label">Password</label>
<div class="col-md-6">
<input type="password" class="form-control" name="password">
</div>
 </div>

 <div class="form-group">
 <label class="col-md-4 control-label">Confirm Password</label>
 <div class="col-md-6">
 <input type="password" class="form-control" name="password_confirmation">
 </div>
  </div>

 <div class="form-group">
 <div class="col-md-6 col-md-offset-4">
 <button type="submit" class="btn btn-primary">
             Reset Password
 </button>
 </div>
 </div>
 </form>

 </div>
 </div>
 </div>
 </div>
</div>

@endsection

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>

				<script>
		
		   $('input,textarea').focus(function(){
		   $(this).data('placeholder',$(this).attr('placeholder'))
		   $(this).attr('placeholder','');
			});
				
				$('input,textarea').blur(function(){
			    $(this).attr('placeholder',$(this).data('placeholder'));
				});
			
		</script>
		
		<script>

		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-63395074-1', 'auto');
	  ga('send', 'pageview');

		</script>
		
    </body>
</html>