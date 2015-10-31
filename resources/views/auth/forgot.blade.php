<!DOCTYPE html>
<html class="bg-black">
    <head>
        <meta charset="UTF-8">
        <title>Hello Photo | Author</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
     



    </head>
    <body class="bg-black">

    <h2> Hi  </h2>

    <div>
        
        Please click this link below to reset your password:<br/> <br/>

        {{ url('password/reset/'.$token) }} <br/> <br/>
        
        This Link Will Auto Expire in 60 Minutes.


        Thank you, <br/>

        The DoorChat Team <br/>
        info@doorchat.com <br/>
        @doorchatrapp <br/>
        facebook.com/doorchat <br/>
    </div>


        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>

    </body>
</html>