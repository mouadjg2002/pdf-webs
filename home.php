<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Check if the file parameter is set and valid
if (isset($_GET['file']) && !empty($_GET['file'])) {
    $filename = $_GET['file'];
    $pdfPath = 'uploads/' . $filename;
} else {
    // If file parameter is not set, redirect back to upload page
    header('Location: upload.php');
    exit;
}

// Extract text from the uploaded PDF file
$pdfText = extractTextFromPDF($pdfPath);

// Function to extract text from PDF using PDF.js
function extractTextFromPDF($pdfPath) {
    require 'vendor/autoload.php'; // Include Composer autoloader

    // Create instance of PDF parser
    $parser = new Smalot\PdfParser\Parser();

    try {
        // Parse PDF file
        $pdf = $parser->parseFile($pdfPath);

        // Get PDF text
        $text = $pdf->getText();
        return $text;
    } catch (Exception $e) {
        // Handle error, if any
        echo 'Error: ' . $e->getMessage();
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer and Chat</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            background-color: #818589; /* Background color */
        }
        .wrapper {
            display: flex;
            flex: 1;
        }
        .pdf-viewer {
            width: 50%; /* Left side for PDF viewer */
            padding: 20px;
            overflow-y: auto;
            margin-top: 50px; /* Adjusted margin */
        }
        .chat-container {
            width: 50%; /* Right side for chat */
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-end; /* Align chat inputs to the bottom */
        }
        .chat-inputs-container {
            display: flex;
            align-items: center;
            margin-top: 10px; /* Add margin to separate from chat messages */
        }
        .chat-inputs-container textarea {
            flex: 1;
            height: 40px;
            margin-right: 10px;
            padding: 5px;
            border: 2px solid #323232; /* Textarea border color */
            border-radius: 5px;
            resize: none;
            box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); /* Add shadow */
        }
        .send-msg-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            background-color: #323232; /* Send button background color */
            color: #ffffff; /* Send button text color */
            font-size: 16px;
            cursor: pointer;
            box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); /* Add shadow */
        }
        .logout-btn,
        .return-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            background-color: #323232; /* Button background color */
            color: #ffffff; /* Button text color */
            font-size: 16px;
            cursor: pointer;
            position: fixed; /* Position buttons */
            top: 10px; /* Distance from top */
            box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); /* Add shadow */
        }
        .logout-btn {
            right: 10px; /* Align to the right */
        }
        .return-btn {
            left: 10px; /* Align to the left */
        }
        .message {
            margin-bottom: 10px; /* Add space between messages */
        }
        .user-message {
            align-self: flex-end; /* Align user messages to the right */
            background-color: #4CAF50; /* User message background color */
            color: white; /* User message text color */
        }
        .bot-message {
            align-self: flex-start; /* Align bot messages to the left */
            background-color: #008CBA; /* Bot message background color */
            color: white; /* Bot message text color */
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="pdf-viewer">
            <?php
            // Display the PDF viewer
            if (isset($_GET['file']) && !empty($_GET['file'])) {
                $filename = $_GET['file'];
                $pdfPath = 'uploads/' . $filename;
                echo "<embed src='$pdfPath' type='application/pdf' width='100%' height='100%' id='pdf-viewer' />";
            } else {
                // If file parameter is not set, redirect back to upload page
                header('Location: upload.php');
                exit;
            }
            ?>
        </div>
        <div class="chat-container" id="chat-container">
            <!-- Chat messages go here -->
        </div>
    </div>
    <div id="chat-form">
        <div class="chat-inputs-container">
            <textarea id="message-input" placeholder="Send a message"></textarea>
            <button class="send-msg-btn" type="button">Send</button>
        </div>
    </div>
    <form method="post">
        <button class="logout-btn" type="submit" name="logout">Log Out</button>
    </form>
    <form action="upload.php">
        <button class="return-btn">Return</button>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.js"></script>
    <script>
    let pdfContent = ''; // Variable to store PDF content

    // Function to send a message to the AI model
    async function sendMessage(message) {
        try {
            const requestBody = {
                model: "gpt-3.5-turbo",
                messages: [{ role: "user", content: message }],
                temperature: 0.7
            };

            const response = await fetch('https://api.openai.com/v1/chat/completions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer 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' // Replace with your OpenAI API key
                },
                body: JSON.stringify(requestBody)
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();

            if (data && data.choices && data.choices.length > 0) {
                return data.choices[0].message.content;
            } else {
                throw new Error('Invalid response from API');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            throw new Error('Error sending message:', error);
        }
    }

    // Function to handle sending message to AI
    async function sendMessageHandler() {
        const messageInput = document.querySelector('#message-input');
        const message = messageInput.value.trim();
        if (message !== '') {
            try {
                // Display user message
                addMessageToChat(message, true);

                // Send message to AI based on PDF content
                const response = await sendMessage(message + ' ' + pdfContent);
                addMessageToChat(response, false);

                messageInput.value = '';
            } catch (error) {
                console.error('Error sending message:', error);
                addMessageToChat('Sorry, there was an error processing your message, please try again later.', false);
            }
        }
    }

    // Function to add messages to the chat container
    function addMessageToChat(message, isUser) {
        const chatContainer = document.querySelector('.chat-container');
        const messageDiv = document.createElement('div');
        messageDiv.textContent = message;
        messageDiv.classList.add('message', isUser ? 'user-message' : 'bot-message');
        chatContainer.appendChild(messageDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Event listener for sending message button
    const sendButton = document.querySelector('.send-msg-btn');
    sendButton.addEventListener('click', sendMessageHandler);

    // Event listener for submitting message form
    const chatForm = document.querySelector('#chat-form');
    chatForm.addEventListener('submit', function(event) {
        event.preventDefault();
        sendMessageHandler();
    });

    // Function to extract text from PDF and store it
    async function extractTextAndStore(pdfUrl) {
        try {
            const pdfText = await extractTextFromPDF(pdfUrl);
            pdfContent = pdfText; // Store the PDF content
        } catch (error) {
            console.error('Error extracting text from PDF:', error);
        }
    }

    // Function to extract text from PDF using PDF.js
    async function extractTextFromPDF(pdfUrl) {
        const loadingTask = pdfjsLib.getDocument(pdfUrl);
        const pdf = await loadingTask.promise;
        let text = '';
        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            const page = await pdf.getPage(pageNumber);
            const pageText = await page.getTextContent();
            pageText.items.forEach(item => {
                text += item.str + ' ';
            });
        }
        return text;
    }

    // Automatically extract text from PDF when the page loads
    window.addEventListener('DOMContentLoaded', function() {
        const pdfViewer = document.getElementById('pdf-viewer');
        extractTextAndStore(pdfViewer.src);
    });
</script>
</body>
</html>
