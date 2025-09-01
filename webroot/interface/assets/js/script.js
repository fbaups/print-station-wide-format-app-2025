document.addEventListener("DOMContentLoaded", () => {
    // TABLE SCROLL ON BUTTON CLICK
    const scrollAmount = 300;
    const scrollLeftButton = document.querySelector(".scroll-button.left");
    const scrollRightButton = document.querySelector(".scroll-button.right");
    const tableContentHolder = document.querySelector(
        ".table-content-holder .tab-content"
    );

    if (scrollLeftButton) {
        scrollLeftButton.addEventListener("click", () => {
            tableContentHolder.scrollBy({
                left: -scrollAmount,
                behavior: "smooth",
            });
        });
    }

    if (scrollRightButton) {
        scrollRightButton.addEventListener("click", () => {
            tableContentHolder.scrollBy({
                left: scrollAmount,
                behavior: "smooth",
            });
        });
    }
});

$(document).ready(function () {
    // PASSWORD VISIBILITY TOGGLE
    $(".pwd-toggle-btn").on("click", function () {
        const inputElement = $(this)
            .closest(".pwd-form-control-wrapper")
            .find("input");
        inputElement.attr(
            "type",
            inputElement.attr("type") === "password" ? "text" : "password"
        );
        $(this).toggleClass("visible");
    });

    // SIDEBAR TOGGLE
    const contentSidebar = $(".content-sidebar");
    const sidebarOverlay = $(".sidebar-overlay");
    $("#sidebar-open-btn, #sidebar-close-btn, .sidebar-overlay").on(
        "click",
        function () {
            contentSidebar.toggleClass("show");
            sidebarOverlay.toggleClass("show");
        }
    );

    //   REMOVE ANIMATION & TRANSITION ON WIN-RESIZE
    let resizeTimer;
    window.addEventListener("resize", () => {
        document.body.classList.add("resize-animation-stopper");
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            document.body.classList.remove("resize-animation-stopper");
        }, 400);
    });

    /* **** LIGHT/DARK MODE TOGGLE *** */
    const darkModeClass = "dark-mode";
    const lightModeClass = "light-mode";
    const prefersDarkMode =
        window.matchMedia &&
        window.matchMedia("(prefers-color-scheme: dark)").matches;
    const savedThemeMode =
        localStorage.getItem("themeMode") || (prefersDarkMode ? "dark" : "light");

    function setThemeMode(mode) {
        const body = document.body;
        body.classList.remove(darkModeClass, lightModeClass);
        body.classList.add(mode === "dark" ? darkModeClass : lightModeClass);
        localStorage.setItem("themeMode", mode);
    }

    setThemeMode(savedThemeMode);
    $("#modeToggle").prop("checked", savedThemeMode === "dark");

    $("#modeToggle").change(function () {
        setThemeMode(this.checked ? "dark" : "light");
    });
    /* **** END OF LIGHT/DARK MODE TOGGLE **** */

    // VIDEO PLAY/PAUSE
    $(".vid-play-btn").on("click", function () {
        const video = $(this).closest(".modal-video-wrapper").find("video")[0];
        video.paused ? video.play() : video.pause();
    });

    $(".video-play-modal").click(function (event) {
        const target = $(event.target);
        if (
            target.hasClass("btn-video-modal-close") ||
            target.hasClass("video-play-modal")
        ) {
            const video = $(this).find("video")[0];
            video.pause();
        }
    });

    // TABLE ROW COLLAPSE/EXPAND
    $(".tbl-job-queues").on("click", function (event) {
        const target = $(event.target);
        if (target.hasClass("row-toggle-btn") || target.is("i.bi-chevron-down")) {
            const currentExpandRowBtn = target.hasClass("row-toggle-btn")
                ? target
                : target.closest(".tbl-actions").find(".row-toggle-btn");
            const currentRowDiv = currentExpandRowBtn.closest("tr");
            currentRowDiv.find("td").toggleClass("border-0");

            if (currentRowDiv.next().hasClass("row-toggle-content")) {
                const currentContentDiv = currentRowDiv.next();
                currentContentDiv.find(".row-toggle-data").slideToggle();
            }
        }
    });

    $(".row-toggle-content").find("td").addClass("border-0");

    $(".app-job-queues-list").on("click", ".row-toggle-btn", function (event) {
        event.stopPropagation();
        const currentButton = $(this);
        const currentRowHead = currentButton.closest(".row-toggle-head");
        const currentRowContent = currentRowHead.next(".row-toggle-content");
        currentButton.find("[class*='icon']").toggleClass("rotate-180deg");
        const isVisible = currentRowContent.find(".row-toggle-data").is(":visible");
        currentRowContent.find(".row-toggle-data").slideToggle();
        currentRowHead.find("td").addClass("border-0");
        currentRowContent.find("td").removeClass("border-0");

        if (!isVisible) {
            $(".row-toggle-content")
                .not(currentRowContent)
                .find(".row-toggle-data")
                .slideUp();
            $(".row-toggle-head")
                .not(currentRowHead)
                .find("td")
                .removeClass("border-0");

            $(".row-toggle-btn")
                .not(currentButton)
                .find("[class*='icon']")
                .removeClass("rotate-180deg");
        }
    });

    if ($(".form-select2").length > 0) {
        $(".form-select2").select2();
    }

    if ($(".datepicker").length > 0) {
        $(".datepicker").datepicker({});
    }
});

/**** #### IMAGE RULER & CONTAINER #### ****/
function createRuler(container, orientation, totalLength, numIntervals) {
    const intervalStep = 10; // Create a tick every 10 intervals
    for (let i = 0; i <= numIntervals; i += intervalStep) {
        const majorTick = document.createElement("div");
        majorTick.classList.add("tick", "major");

        if (i % 100 === 0) {
            majorTick.classList.add("distinguished");
        } else if (i % 50 === 0) {
            majorTick.classList.add("visible");
        }

        if (i % 100 === 0) {
            const majorLabel = document.createElement("div");
            majorLabel.classList.add("label");
            majorLabel.textContent = i;
            if (orientation === "horizontal") {
                majorLabel.style.transform = "translateX(-50%)";
            } else {
                majorLabel.classList.add("vertical");
                majorLabel.style.transform = "translateY(-50%)";
            }
            majorTick.appendChild(majorLabel);
        }

        let containerHeight = container.offsetHeight;
        let containerWidth = container.offsetWidth;

        if (orientation === "horizontal") {
            majorTick.style.width = `${
                (containerWidth / numIntervals) * intervalStep
            }px`;
        } else {
            majorTick.classList.add("vertical");

            majorTick.style.height = `${
                (containerHeight / numIntervals) * intervalStep
            }px`;
        }

        container.appendChild(majorTick);
    }
}

let scalingPercent = 100;

let scalingInput = document.getElementById("scaling-input");

if (scalingInput) {
    scalingInput.addEventListener("change", function (event) {
        let value = event.target.value.replace("%", "");
        let numericValue = parseInt(value);

        if (isNaN(numericValue)) {
            scalingPercent = 100;
        } else {
            scalingPercent = numericValue;
        }

        event.target.value = scalingPercent;
        resizeRulersAndImageOnPaperRoll();
    });
}

let imageDimensions = {
    widthPixels: null, //this is the pixel dimension of the image - will be calculated naturalWidth
    heightPixels: null, //this is the pixel dimension of the image - will be calculated naturalHeight
    widthPrintMm: 500, //I want the image to print this wide on the paper roll
    heightPrintMm: null, //will be calculated based image ratio and widthPrintMm
};

// I will dynamically populate the following variables
let paperRoll = {
    widthMm: 600, // the paper roll is physically this wide - could be 600/900/1200mm
    heightMm: null, // will be calculated based on the height of the image
};

function resizeRulersAndImageOnPaperRoll() {
    let scalingFactor = scalingPercent / 100;

    //calculate image dimensions
    const image = document.querySelector(".content img");
    imageDimensions["widthPixels"] = image.naturalWidth;
    imageDimensions["heightPixels"] = image.naturalHeight;
    imageDimensions["heightPrintMm"] =
        (imageDimensions["heightPixels"] / imageDimensions["widthPixels"]) *
        imageDimensions["widthPrintMm"];

    //apply scaling to print dimensions
    let calculatedPrintWidthMm = imageDimensions["widthPrintMm"] * scalingFactor;
    let calculatedPrintHeightMm =
        imageDimensions["heightPrintMm"] * scalingFactor;

    //calculate roll height based on image print size - rounded up to nearest 100
    paperRoll["heightMm"] = Math.ceil(calculatedPrintHeightMm / 100) * 100;

    //adjust the element that holds the ruler
    let rulerMainContainer = document.querySelector(".ruler-main-container");
    let rulerMainContainerWidthPixels = parseFloat(
        getComputedStyle(rulerMainContainer).width
    );
    let rulerMainContainerHeightPixels =
        (paperRoll["heightMm"] / paperRoll["widthMm"]) *
        rulerMainContainerWidthPixels;
    rulerMainContainer.style.height = `${rulerMainContainerHeightPixels}px`;

    //determine the size of the element that holds the image
    let rollImgHolder = document.querySelector("#roll-img-holder");
    let rollImgHolderWidthPixels = parseFloat(
        getComputedStyle(rollImgHolder).width
    );

    //adjust the rulers
    const horizontalRuler = document.getElementById("horizontal-ruler");
    const verticalRuler = document.getElementById("vertical-ruler");
    horizontalRuler.innerHTML = "";
    verticalRuler.innerHTML = "";
    horizontalRuler.style.width = `100%`;
    verticalRuler.style.height = `100%`;
    createRuler(
        horizontalRuler,
        "horizontal",
        paperRoll["widthMm"],
        paperRoll["widthMm"]
    );
    createRuler(
        verticalRuler,
        "vertical",
        paperRoll["heightMm"],
        paperRoll["heightMm"]
    );

    //calculate how big in pixels the image needs to be displayed on the roll
    let rulerLineWidth = 2; //the ruler has a 2px wide line that needs to be compensated for
    let rollImageWidthPixels =
        (calculatedPrintWidthMm / paperRoll["widthMm"]) * rollImgHolderWidthPixels;
    let rollImageHeightPixels =
        (rollImageWidthPixels / imageDimensions["widthPixels"]) *
        imageDimensions["heightPixels"];
    // compensation for the tick mark width
    let compWidth =
        (document.querySelector(".ruler.horizontal .tick.major.distinguished")
                .clientWidth /
            paperRoll["widthMm"]) *
        calculatedPrintWidthMm;
    let compHeight =
        (document.querySelector(".ruler.vertical .tick.major.distinguished")
                .clientHeight /
            paperRoll["heightMm"]) *
        calculatedPrintHeightMm;
    rollImageWidthPixels = rollImageWidthPixels - compWidth - rulerLineWidth;
    rollImageHeightPixels = rollImageHeightPixels - compHeight - rulerLineWidth;

    //adjust the image size
    let rollImg = document.querySelector(".roll-img");
    rollImg.style.width = `${Math.ceil(rollImageWidthPixels)}px`;
    rollImg.style.height = `${Math.ceil(rollImageHeightPixels)}px`;
    document.querySelector(".roll-img img").style.width = "100%";

    // Adjust container heights
    document.querySelector(
        ".jobfile-edit-img-container"
    ).style.minHeight = `calc(${rulerMainContainerHeightPixels}px + 160px)`;
    document.querySelector(
        ".roll > .roll-child:nth-child(3)"
    ).style.minHeight = `calc(${rulerMainContainerHeightPixels}px + 80px)`;
}

document.addEventListener("DOMContentLoaded", () => {
    const image = document.querySelector(".content img");
    if (image) {
        if (image.complete) {
            resizeRulersAndImageOnPaperRoll();
        } else {
            image.addEventListener("load", () => {
                resizeRulersAndImageOnPaperRoll();
            });
        }

        window.addEventListener("resize", () => {
            resizeRulersAndImageOnPaperRoll();
        });
    }
});
