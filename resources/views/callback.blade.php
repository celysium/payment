<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@500&display=swap" rel="stylesheet">

    <title>Callback local gateway</title>
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            font-size: 20px;
        }

        .container-fluid {
            padding: 1rem;
            margin: auto;
        }

        .mt-4 {
            margin-top: 1.5rem !important;
        }

        .center {
            text-align: center;
        }

        .d-none {
            display: none;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: center;
            text-decoration: none;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border-radius: 50rem !important;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .success {
            color: #37c481 !important;
        }

        .btn.success {
            color: #fff !important;
            background-color: #37c481 !important;
        }

        .btn.success:hover {
            background-color: #28a66c;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="mt-4 center">
        <form id="form" method="GET" class="{{ cache('quick') ? 'd-none' : '' }}" action="{{ cache('callbackUrl') }}">
            <div>در حال بازگشت به سایت پذیرنده <span id="countdown">3</span> ثانیه</div>
            <input type="hidden" name="id" value="{{ cache('id') }}">
            <input type="hidden" name="driver" value="local">

            <button class="btn success mt-4" type="submit">تکمیل پرداخت</button>
        </form>
    </div>
</div>
<script>
    let seconds = 3;

    function countdown() {
        seconds = seconds - 1;
        if (seconds <= 0) {
            // submit the form
            submitForm();
        } else {
            // Update remaining seconds
            document.getElementById("countdown").innerHTML = seconds;
            // Count down using javascript
            window.setTimeout("countdown()", 1000);
        }
    }

    function submitForm() {
        document.getElementById('form').submit();
    }

    @if(cache('quick'))
    submitForm();
    @endif
    countdown();
</script>
</body>
</html>

