<html lang="en">
<head>
<meta charset="utf-8">
<title>TEST</title>
<script src="test.js"></script>
<style>
.red {
	color: red;
	border: 1px solid #555;
}
</style>
</head>
<body>
    <p>
        <form id="login">
            <strong>Login</strong><br/><br/>
            Username :<br/><input type="text" name="uname" id="uname" /><br/>
            Password :<br/><input type="password" name="passwd" id="passwd" /><br/><br/>
            <div id="message" style="color:red;"></div>
            <br/><button type="button" value="Submit" onclick="testPass();">Sign In</button>
        </form>
    </p>
	<div id="texthere">Change here</div>
</body>
</html>