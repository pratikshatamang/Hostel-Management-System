<!DOCTYPE html>
<html>
<head>
    <title>Khalti SDK Diagnostic</title>
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.15.0.0.0/khalti-checkout.iffe.js"></script>
    <style>
        body { font-family: sans-serif; padding: 50px; text-align: center; }
        .status { padding: 20px; border-radius: 8px; margin: 20px 0; font-weight: bold; }
        .success { background: #e6fffa; color: #2c7a7b; border: 1px solid #b2f5ea; }
        .error { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
        .btn { background: #5C2D91; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 18px; }
    </style>
</head>
<body>
    <h1>Khalti SDK Diagnostic Tool</h1>
    <p>This page tests if the Khalti global variable is loaded correctly.</p>

    <div id="sdk-status" class="status">Checking SDK...</div>

    <button id="test-btn" class="btn" style="display:none;">Launch Test Widget (Rs 10)</button>

    <script>
        window.onload = function() {
            var statusDiv = document.getElementById('sdk-status');
            var btn = document.getElementById('test-btn');

            if (typeof KhaltiCheckout !== "undefined") {
                statusDiv.innerText = "SUCCESS: Khalti SDK Loaded correctly!";
                statusDiv.className = "status success";
                btn.style.display = "inline-block";

                var config = {
                    "publicKey": "25df58e4b6064975ae40c03a799fec58",
                    "productIdentity": "TEST_123",
                    "productName": "Diagnostic Test",
                    "productUrl": "http://example.com",
                    "eventHandler": {
                        onSuccess (payload) { console.log(payload); alert("Success!"); },
                        onError (error) { console.log(error); alert("Error: Check Console"); },
                        onClose () { console.log('Closed'); }
                    }
                };

                var checkout = new KhaltiCheckout(config);
                btn.onclick = function() {
                    console.log("Attempting to show checkout modal...");
                    try {
                        checkout.show({amount: 1000});
                    } catch(e) {
                        alert("FAILED to launch: " + e.message);
                    }
                }
            } else {
                statusDiv.innerText = "ERROR: Khalti SDK Variable not found! Your browser or firewall might be blocking the CDN script.";
                statusDiv.className = "status error";
            }
        };
    </script>
</body>
</html>
