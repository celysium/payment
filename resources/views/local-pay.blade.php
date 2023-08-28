<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Local payment gateway</title>
</head>
<body>
<form id="form" method="POST" action="{{ $callback }}">
    <input type="hidden" name="transactionId" value="{{ cache('transactionId') }}">
    <input type="hidden" name="id" value="{{ cache('id') }}">
        <input type="hidden" name="status" id="action_type" value="">

        <input type="text" value="{{ cache('amount') }}" name="amount" disabled> <span> is correct ?</span>

        <a class="button button-primary" onClick="submitForm(1)">Pay</a>
        <hr>
        <a class="button button-error" onClick="submitForm(2)">Cancel</a>
</form>
<script>
    function submitForm(type) {
        document.getElementById('action_type').value = type;
        document.getElementById('form').submit();
    }
</script>
</body>
</html>
