<?php
if (isset($_FILES['myFile'])) {
    print_r($_FILES);exit;
       move_uploaded_file($_FILES['myFile']['tmp_name'], "./data/" . $_FILES['myFile']['name']);
    exit;}?><!DOCTYPE html><html><head>
    <title>dnd binary upload</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <script type="text/javascript">
        function sendFile(file) {
            var uri = "./uploadFiled.php";
            var xhr = new XMLHttpRequest();
            var fd = new FormData();
            
            xhr.open("POST", uri, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                                       alert(xhr.responseText);                }
            };
            fd.append('myFile', file);
                       xhr.send(fd);
        }

        window.onload = function() {
            var dropzone = document.getElementById("dropzone");
            dropzone.ondragover = dropzone.ondragenter = function(event) {
                event.stopPropagation();
                event.preventDefault();
            }
    
            dropzone.ondrop = function(event) {
                event.stopPropagation();
                event.preventDefault();

                var filesArray = event.dataTransfer.files;
                for (var i=0; i<filesArray.length; i++) {
                    sendFile(filesArray[i]);
                }
            }
        }
    </script></head><body>
    <div>
        <div id="dropzone" style="margin:30px; width:500px; height:300px; border:1px dotted grey;">Drag & drop your file here...</div>
    </div></body></html>

