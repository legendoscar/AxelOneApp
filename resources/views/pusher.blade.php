<!DOCTYPE html>

<head>
    <title>Pusher Test</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('41fdd8aea06bdfcaf6e6', {
            cluster: 'us2'
        });

        var channel = pusher.subscribe('leads');
        channel.bind('NewLeadCreated', function(data) {
            alert(JSON.stringify(data));
        });
    </script>
</head>

<body>
    <h1>Pusher Test</h1>
    <p>
        Try publishing an event to channel <code>leads</code>
        with event name <code>NewLeadCreated</code>.
    </p>
</body>
