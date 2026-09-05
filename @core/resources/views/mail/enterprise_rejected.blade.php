<!DOCTYPE html>
<html>
<head>
    <title>Enterprise Rejected</title>
</head>
<body>
    <h1>Dear {{ $enterprise->name }},</h1>
    <p>We regret to inform you that your enterprise registration has been rejected.</p>
    <p><strong>Reason for Rejection:</strong> {{ $rejectionReason }}</p>
    <p>If you have any questions, please contact our support team.</p>
</body>
</html>
