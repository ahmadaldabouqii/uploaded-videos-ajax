<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
    />
    <title></title>
</head>
<body>
<div class="w-80 mx-auto mt-5 p-7">
    <button
        class="bg-green-500 text-white rounded-md px-8 py-2 text-base font-medium hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300"
        id="open-btn">
        Choose Videos
    </button>
</div>

<div class="hidden z-10" id="my-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
            <div
                class="flex justify-center bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full sm:p-6">
                <div class="hidden sm:block absolute top-0 right-0 p-3">
                    <button id="ok-btn" type="button"
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div id="content" class="flex flex-col justify-start align-center"></div>
                <div class="w-full">
                    <div class="w-full">
                        <div class="flex justify-center align-center p-5">
                            <form class="w-full flex flex-col items-center justify-center" id="form" enctype="multipart/form-data">
                                <label class="w-22 p-10 border-4 border-dashed border-sky-900 my-8 rounded-lg flex flex-col align-center justify-center cursor-pointer">
                                    <input class="file-input" type="file" accept="video/*" name="videos" multiple hidden/>
                                    <i class="text-5xl fas fa-cloud-upload-alt"></i>
                                    <p>Browse File to Upload</p>
                                </label>
                                <div class="w-full max-h-60 overflow-y-scroll flex flex-col items-start mt-5">
                                    <section class="progress-area flex flex-col items-start"></section>
                                    <section class="uploaded-area max-h-60 flex flex-col items-start"></section>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

<script>

    // Grabs all the Elements by their IDs which we had given them
    let modal  = document.getElementById("my-modal");
    let btn    = document.getElementById("open-btn");
    let button = document.getElementById("ok-btn");

    document.getElementById("form").addEventListener("submit", function(event){
        event.preventDefault();
        console.log("clicked");
    });

    // We want the modal to open when the Open button is clicked
    btn.addEventListener('click', function () {
        modal.style.display = "block";
    });

    // We want the modal to close when the OK button is clicked
    button.addEventListener('click', function () {
        modal.style.display = "none";
    });

    const form = document.querySelector("#form"),
        fileInput    = document.querySelector(".file-input"),
        progressArea = document.querySelector(".progress-area"),
        uploadedArea = document.querySelector(".uploaded-area")
    ;

    var data = new FormData();

    fileInput.onchange = ({target}) => {
        let files = target.files.length;

        for (let i = 0; i < files; i++) {
            if (target.files[i]) {
                let fileName = target.files[i]["name"];
                if (fileName.length >= 12) {
                    let splitName = fileName.split(".");
                    fileName = splitName[0].substring(0, 13) + "... ." + splitName[1];
                }
            }
            var ajax_request = new XMLHttpRequest();

            ajax_request.open("POST", "{{route("uploaded")}}");
            ajax_request.setRequestHeader("X-CSRF-TOKEN", document.querySelector('meta[name="csrf-token"]').content);

            if (ajax_request.upload) {
                ajax_request.upload.addEventListener("progress", ({loaded, total}) => {
                    let fileLoaded = Math.floor((loaded / total) * 100);
                    let fileTotal  = Math.floor(total / 1000);
                    let fileSize;
                    fileTotal < 1024 ? (fileSize = fileTotal + " KB") : (fileSize = (loaded / (1024 * 1024)).toFixed(2) + " MB");

                    if (loaded === total) {
                        progressArea.innerHTML = "";

                        let uploadedHTML = `
                            <div class="flex justify-center items-center">
                                <li class="flex items-center justify-between list-none bg-row py-2.5 px-5 rounded-md mb-2 w-22 mr-2">
                                    <div class="w-full flex justify-start items-center upload">
                                         <i class="blue-sky text-3xl fas fa-file-alt"></i>
                                         <div class="flex ml-4 flex-col items-start mb-2 justify-between">
                                              <span class="text-sm name">${target.files[i]["name"]} • Uploaded</span>
                                              <span class="text-sm bg-gray-1 text-xs">${fileSize}</span>
                                         </div>
                                    </div>
                                <i class="text-base border-sky-900 fas fa-check"></i>
                                </li>
                                <input type="text" class="mr-2 border border-solid border-gray-300 rounded transition ease-in-out" name="name" value="${target.files[i]["name"]}"/>
                                <input accept="image/*" class="mr-2 block text-sm text-gray-900 rounded-lg border cursor-pointer dark:text-gray-400 focus:outline-none" name="video_image" id="file_input" type="file" />
                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">submit</button>
                            </div>
                        `;
                        uploadedArea.classList.remove("max-h-38");
                        uploadedArea.insertAdjacentHTML("afterbegin", uploadedHTML);
                    } else {
                        let progressHTML = `
                            <div class="flex justify-center items-center">
                                <li class="flex items-center justify-between list-none bg-row py-2.5 px-5 rounded-md mb-2 w-22 mr-2">
                                    <i class="blue-sky text-3xl fas fa-file-alt"></i>
                                    <div class="w-full ml-4">
                                         <div class="flex mb-2 justify-between">
                                              <span class="text-sm name">${target.files[i]["name"]} • Uploading</span>
                                              <span class="percent">${fileLoaded}%</span>
                                         </div>
                                         <div class="w-full h-1 mb-1 bg-white rounded-4xl">
                                              <div class="h-full w-0 bg-blue-sky border-radius-inherit" style="width: ${fileLoaded}%"></div>
                                         </div>
                                    </div>
                                </li>
                                <input accept="image/*" disabled class="mr-2 border border-solid border-gray-300 rounded transition ease-in-out" type="text" id="video_name" name="name" value="${target.files[i]["name"]}"/>
                                <input class="mr-2 block text-sm text-gray-900 rounded-lg border cursor-pointer dark:text-gray-400 focus:outline-none" id="file_input" name="video_image" type="file" disabled />
                                <button disabled type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">submit</button>
                            </div>
                        `;
                        uploadedArea.classList.add("max-h-38");
                        progressArea.innerHTML = progressHTML;
                    }
                });
            }

            data = new FormData();
            data.append("videos", target.files[i]);
            ajax_request.send(data);
        }
    };
</script>
</html>
