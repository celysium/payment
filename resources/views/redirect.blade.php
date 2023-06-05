<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forwarding to secure payment provider</title>
</head>
<body onload="submitForm();">
<form id="form" method="{{ $method }}" action="{{ $action }}">
    @foreach($inputs as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <button type="submit">Go</button>
</form>
<script>
    function submitForm() {
        document.getElementById('form').submit()
    }
</script>
</body>
</html>