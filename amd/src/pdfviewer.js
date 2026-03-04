// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * PDF Viewer with protection features
 *
 * @module     mod_fileca/pdfviewer
 * @copyright  2025 CentricApp LTD
 * @author     Dev Team <dev@centricapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function pdfviewer(config) {
  var pdfDoc = null
  var pageNum = 1
  var pageRendering = false
  var pageNumPending = null
  var scale = 1.5
  var canvas = document.getElementById("pdf-canvas")

  if (!canvas) {
    console.error("[v0] PDF canvas element not found")
    return
  }

  var ctx = canvas.getContext("2d")
  var fileurl = config.fileurl
  var enablecopying = config.enablecopying
  var enabledownload = config.enabledownload
  var enablesummarize = config.enablesummarize
  var contextid = config.contextid
  var filecaid = config.filecaid

  // Declare variables before using them
  var $ = window.jQuery // Assuming jQuery is available
  var ajax = window.ajax // Assuming ajax is available
  var notification = window.notification // Assuming notification is available

  var script = document.createElement("script")
  script.src = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"
  script.onload = () => {
    console.log("[v0] PDF.js script loaded")

    // Check if pdfjsLib is available
    if (typeof window.pdfjsLib === "undefined") {
      console.error("[v0] PDF.js library not available after loading")
      return
    }

    // Set worker source
    window.pdfjsLib.GlobalWorkerOptions.workerSrc =
      "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js"

    console.log("[v0] PDF.js configured, loading PDF from:", fileurl)

    // Load the PDF document
    var loadingTask = window.pdfjsLib.getDocument(fileurl)
    loadingTask.promise
      .then((pdf) => {
        pdfDoc = pdf
        console.log("[v0] PDF loaded successfully, pages:", pdf.numPages)
        renderPage(pageNum)
      })
      .catch((error) => {
        console.error("[v0] Error loading PDF:", error)
      })
  }

  script.onerror = () => {
    console.error("[v0] Failed to load PDF.js library")
  }

  document.head.appendChild(script)

  function renderPage(num) {
    pageRendering = true
    pdfDoc.getPage(num).then((page) => {
      var viewport = page.getViewport({ scale: scale })
      canvas.height = viewport.height
      canvas.width = viewport.width

      var renderContext = {
        canvasContext: ctx,
        viewport: viewport,
      }

      var renderTask = page.render(renderContext)
      renderTask.promise.then(() => {
        pageRendering = false
        if (pageNumPending !== null) {
          renderPage(pageNumPending)
          pageNumPending = null
        }
      })
    })

    $("#pdf-page-info").text("Page " + num + " / " + pdfDoc.numPages)
  }

  function queueRenderPage(num) {
    if (pageRendering) {
      pageNumPending = num
    } else {
      renderPage(num)
    }
  }

  function onPrevPage() {
    if (pageNum <= 1) {
      return
    }
    pageNum--
    queueRenderPage(pageNum)
  }

  function onNextPage() {
    if (pageNum >= pdfDoc.numPages) {
      return
    }
    pageNum++
    queueRenderPage(pageNum)
  }

  function extractPdfText() {
    var textPromises = []
    for (var i = 1; i <= pdfDoc.numPages; i++) {
      textPromises.push(
        pdfDoc
          .getPage(i)
          .then((page) => page.getTextContent())
          .then((textContent) => textContent.items.map((item) => item.str).join(" ")),
      )
    }
    return Promise.all(textPromises).then((pages) => pages.join("\n"))
  }

  function summarizeDocument() {
    var $btn = $("#summarize-btn")
    var $result = $("#summary-result")

    $btn.prop("disabled", true).text("Summarizing...")
    $result.hide()

    extractPdfText().then((fullText) => {
      var promises = ajax.call([
        {
          methodname: "mod_fileca_generate_summary",
          args: {
            contextid: contextid,
            filecaid: filecaid,
            content: fullText.substring(0, 10000),
          },
        },
      ])

      promises[0]
        .done((response) => {
          if (response.success) {
            $result
              .html('<div class="alert alert-success"><h4>Summary</h4><p>' + response.summary + "</p></div>")
              .show()
          } else {
            notification.addNotification({
              message: "Failed to generate summary",
              type: "error",
            })
          }
        })
        .fail(() => {
          notification.addNotification({
            message: "Failed to generate summary",
            type: "error",
          })
        })
        .always(() => {
          $btn.prop("disabled", false).text("Summarize")
        })
    })
  }

  // Navigation buttons
  $("#pdf-prev").on("click", onPrevPage)
  $("#pdf-next").on("click", onNextPage)

  // Summarize button
  if (enablesummarize) {
    $("#summarize-btn").on("click", summarizeDocument)
  }

  // Apply protections
  if (!enablecopying) {
    $("#pdf-container").css({
      "-webkit-user-select": "none",
      "-moz-user-select": "none",
      "-ms-user-select": "none",
      "user-select": "none",
    })
    $("#pdf-container").on("copy", (e) => {
      e.preventDefault()
      return false
    })
  }

  if (!enabledownload) {
    $("#pdf-container").on("contextmenu", (e) => {
      e.preventDefault()
      return false
    })
  }
}

// Example usage:
// pdfviewer({fileurl: 'path/to/pdf', enablecopying: false, enabledownload: false, enablesummarize: true, contextid: 123, filecaid: 456});
