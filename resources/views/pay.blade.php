<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@500&display=swap" rel="stylesheet">

    <title>Pay local gateway</title>
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

        .mx-2 {
            margin-right: 0.75rem !important;
            margin-left: 0.75rem !important;
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

        .danger {
            color: #ff4659 !important;
        }

        .btn.danger {
            color: #fff !important;
            background-color: #ff4659;
        }

        .btn.danger:hover {
            background-color: #ea3143;
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
        <form id="form" method="GET" class=" {{ cache('quick') ? 'd-none' : '' }}"
              action="{{ config('payment.drivers.local.apiCallbackUrl') }}">
            <input type="hidden" id="status" name="status" value="0">
            <div>مهلت پرداخت<span id="countdown"></span> ثانیه</div>
            <div>پرداخت مبلغ:</div>
            <div><b> {{ cache('amount') }}</b> ریال </div>

            <button class="btn success mt-4 mx-2" onClick="submitForm(1)">پرداخت</button>
            <button class="btn danger mt-4 mx-2" onClick="submitForm(2)">انصراف</button>
        </form>
    </div>
</div>
<script>
    function submitForm(type) {
        document.getElementById('status').value = type;
        document.getElementById('form').submit();
    }

    let seconds = 30;

    @if(cache('quick'))
    submitForm(1);
    @endif

    function countdown() {
        seconds = seconds - 1;
        if (seconds <= 0) {
            // submit the form
            submitForm(2);
        } else {
            // Update remaining seconds
            document.getElementById("countdown").innerHTML = seconds;
            // Count down using javascript
            window.setTimeout("countdown()", 1000);
        }
    }

    countdown();
</script>
</body>
</html>

