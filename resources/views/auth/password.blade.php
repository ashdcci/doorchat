 <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<style type="text/css">
  .container-fluid{
    margin:100px auto;

  }
  body{
    background-color: #282f3d;
    color:#646873;
  }
  .btn-primary{
    background-color: #3baff7;
    border:none;
  }
</style>

<div class="container-fluid">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel panel-default">
<div class="panel-heading">Reset Password DoorChat</div>
<div class="panel-body">
    @if (session('status'))
      <div class="alert alert-success">
             {{ session('status') }}
      </div>
    @endif

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

<form class="form-horizontal" role="form" method="POST" action="{{ url('v1/postEmail') }}">
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="form-group">
<label class="col-md-4 control-label">E-Mail Address</label>
<div class="col-md-6">
<input type="email" class="form-control" name="email" value="{{ old('email') }}">
</div>
</div>

<div class="form-group">
<div class="col-md-6 col-md-offset-4">
<button type="submit" class="btn btn-primary">
         Send Password Reset Link
</button>
</div>
</div>
</form>

</div>
</div>
</div>
</div>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>