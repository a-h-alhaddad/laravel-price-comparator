<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload & Analyze Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- TensorFlow.js -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet"></script>

    <style>
       body {
    background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgb(12, 24, 22) 0%, rgba(12, 24, 22) 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh; /* Changed from height: 100vh */
    position: relative;
    overflow-x: hidden; /* Allow vertical scrolling */
}


        /* Background Shapes */
        body::before {
            content: "";
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            filter: blur(100px);
            border-radius: 50%;
            top: 10%;
            left: 10%;
        }

        body::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            filter: blur(80px);
            border-radius: 50%;
            bottom: 10%;
            right: 10%;
        }

        .container {
            background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgb(23, 26, 27) 0%, rgb(25, 62, 56) 100%);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 500px;
            max-width: 90%;
        }

        .btn-custom {
            width: 80%;
            border-radius: 50px;
            background: linear-gradient(45deg, #1f5260, #00879f);
            border: none;
            color: white;
            padding: 10px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-custom:hover {
            transform: scale(1.05);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            text-align: center;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        img {
            border-radius: 10px;
            margin-top: 10px;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <h3 class="text-white mb-4">Upload & Find Best Price</h3>

        <!-- File Upload Input -->
        <div class="mb-3">
            <input type="file" id="imageInput" accept="image/*" class="form-control">
        </div>

        <!-- Image Preview -->
        <img id="preview" class="img-fluid d-none mb-3" style="max-width: 50%;">

        <!-- Upload & Analyze Button -->
        <button onclick="uploadAndAnalyze()" class="btn btn-custom mb-3 mt-2">Upload & Analyze</button>

        <!-- Detected Product -->
        <p class="text-white">Detected Product: <span id="result" class="text-info"></span></p>
        
        <!-- Find Best Price Button -->
        <button onclick="fetchPrices()" class="btn btn-custom">Find Best Price</button>

        <!-- Prices List -->
        <ul id="pricesList" class="list-group mt-3"></ul>
    </div>

    <script>
        async function uploadAndAnalyze() {
            let fileInput = document.getElementById('imageInput');
            let imgPreview = document.getElementById('preview');
            
            if (!fileInput.files.length) {
                alert("Please select an image first!");
                return;
            }

            let reader = new FileReader();
            reader.onload = function (e) {
                imgPreview.src = e.target.result;
                imgPreview.classList.remove("d-none");
            };
            reader.readAsDataURL(fileInput.files[0]);

            let model = await mobilenet.load();
            let predictions = await model.classify(imgPreview);
            let productName = predictions[0].className;
            document.getElementById("result").innerText = productName;

            let formData = new FormData();
            formData.append("product_name", productName);

            let response = await fetch("/upload", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                }
            });

            let data = await response.json();
            console.log("Upload Response:", data);
        }

        async function fetchPrices() {
            let query = document.getElementById('result').innerText;
            if (!query) {
                alert("No product detected. Please upload and analyze an image first.");
                return;
            }

            let response = await fetch(`/search/${encodeURIComponent(query)}`);
            let prices = await response.json();
            let list = document.getElementById("pricesList");
            list.innerHTML = "";

            prices.forEach(item => {
                let li = document.createElement("li");
                li.innerHTML = `<a href="${item.url}" target="_blank" class="list-group-item list-group-item-action">
                    <strong>${item.title}</strong> - <span class="text-success">${item.price}</span>
                </a>`;
                list.appendChild(li);
            });
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
