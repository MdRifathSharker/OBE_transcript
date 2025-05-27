<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Excel File</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f7fc;
            padding-top: 70px;
        }

        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: #2e3458;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .top-header img {
            width: 40px;
            margin-right: 10px;
        }

        .form-container {
            max-width: 500px;
            margin: 100px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .form-group input[type="file"],
        .form-group input[type="number"],
        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .submit-btn {
            background: #2980b9;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        .submit-btn:hover {
            background: #1f6692;
        }
    </style>
</head>
<body>

    <header class="top-header">
        <img src="images/bauet-logo2.png" alt="BAUET Logo">
        <h1><span>Bangladesh Army University of Engineering & Technology</span></h1>
    </header>

    <div class="form-container">
        <h2>Upload Excel File</h2>
        <form action="code_excel_file.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="import_file">Select Excel File:</label>
                <input type="file" name="import_file" id="import_file" accept=".xls,.xlsx,.csv" required>
            </div>

            <div class="form-group">
                <label for="header_row">Header Row (Which row contains column names?):</label>
                <input type="number" name="header_row" id="header_row" min="1" value="1" required>
            </div>

            <div class="form-group">
                <label for="batch_no">Batch No:</label>
                <input type="text" name="batch_no" id="batch_no" required>
            </div>

            <button type="submit" name="save_excel_data" class="submit-btn">Upload</button>
        </form>
    </div>

</body>
</html>