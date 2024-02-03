<!DOCTYPE html>
<html lang="jp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
    <!-- TensorFlow.js library -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <!-- COCO-SSD model -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
    <title>F2</title>
</head>

<style>
    body {
        overflow: auto;
    }

    #result img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
    }

    .button-parent {
        display: flex;
        justify-content: space-around;
    }

    .button-container {
        padding-top: 40px;
        padding-bottom: 30px;
        text-align: center;
        color: #fff;
    }

    .button {
        width: 100px;
        display: inline-block;
        text-align: center;
        text-decoration: none;
        font-size: 1.8em;
        color: #fff;
        background-color: #73a9a8;
        border-radius: 20px;
        transition: background-color 0.3s;
    }

    .button a {
        color: #fff;
        text-decoration: none;
    }
</style>

<body>

    <h1><img src="../css/img/F2.png"></h1>

    <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" id="imageInput" accept="image/*" onchange="detectObjects()">
    </form>

    <div id="result"></div>
    <div id="totalPersonCount"></div>

    <script>

        // 写真の選択または撮影
        var imageInput = document.getElementById("imageInput");
        imageInput.addEventListener("change", handleFiles, false);

        function handleFiles() {
            var fileList = this.files;
            var firstFile = fileList[0];

            var reader = new FileReader();
            reader.onloadend = function () {
                localStorage.setItem('imageData', reader.result);
            }
            reader.readAsDataURL(firstFile);
        }

        async function detectObjects() {
            const file = imageInput.files[0];

            if (!file) {
                alert('画像ファイルを選択してください。');
                return;
            }

            // COCO-SSDモデルの読み込み
            const model = await cocoSsd.load();

            // 画像ファイルをデータURLとして読み込む
            const reader = new FileReader();
            reader.onload = async function (event) {
                const image = new Image();
                image.src = event.target.result;
                image.onload = async function () {
                    // オブジェクト検出を実行
                    const predictions = await model.detect(image);

                    // 結果を表示
                    const resultDiv = document.getElementById('result');

                    // バウンディングボックスを描画するためのキャンバスを作成
                    const canvas = document.createElement('canvas');
                    canvas.width = image.width;
                    canvas.height = image.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(image, 0, 0);

                    if (predictions.length > 0) {
                        let personCount = 0;
                        predictions.forEach(prediction => {
                            if (prediction.class === 'person') {
                                // キャンバスにバウンディングボックスを描画
                                ctx.beginPath();
                                ctx.rect(
                                    prediction.bbox[0],
                                    prediction.bbox[1],
                                    prediction.bbox[2],
                                    prediction.bbox[3]
                                );
                                ctx.lineWidth = 2;
                                ctx.strokeStyle = 'red';
                                ctx.fillStyle = 'transparent';
                                ctx.stroke();
                                ctx.closePath();

                                // 人数をカウント
                                personCount++;
                            }
                        });

                        // バウンディングボックス付きの画像を表示
                        const imgWithBoundingBox = new Image();
                        imgWithBoundingBox.src = canvas.toDataURL();
                        imgWithBoundingBox.style.width = '100%';
                        resultDiv.appendChild(imgWithBoundingBox);

                        // 合計人数を表示
                        const totalPersonCountDiv = document.getElementById('totalPersonCount');
                        totalPersonCountDiv.innerHTML = `<p>検出された総人数: ${personCount}</p>`;

                    } else {
                        resultDiv.innerHTML += '<p>画像に人物は検出されませんでした。</p>';
                    }
                };
            };
            reader.readAsDataURL(file);
        }

        async function sendPersonCount() {
            const totalPersonCountDiv = document.getElementById('totalPersonCount');
            const personCountValue = totalPersonCountDiv.innerText.match(/\d+/)[0];

            // 新しいフォーム要素を作成
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'F4.php';

            if (personCountValue < 1 || personCountValue > 8) {
                alert('プレイ人数は1～8人です');
                return;
            }

            // フォームにhiddenフィールドを追加してpersonCountの値を送信
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'number';
            input.value = personCountValue;
            form.appendChild(input);

            // フォームをbodyに追加してsubmit
            document.body.appendChild(form);
            form.submit();
        }
    </script>

    <div class="button-parent">
        <div class="button-container2">
            <div class="button"><a href="F2.php">リトライ</a></div>
        </div>
        <div class="button-container3">
            <div class="button" onclick="sendPersonCount()">次へ</div>
        </div>
    </div>

</body>

</html>
