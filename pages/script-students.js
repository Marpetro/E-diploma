function openTab(tabId) {
    // Hide all tab content
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(button => {
        button.classList.remove('active');
    });
    // Show the selected tab content
    document.getElementById(tabId).classList.add('active');
    // Set the clicked button as active
    const activeButton = Array.from(buttons).find(button => button.textContent === tabId.replace('-', ' ').replace('under ', ''));
    if (activeButton) {
        activeButton.classList.add('active');
    }
}
   
   function openTab(event, tabId) {
    // Κρύβει όλα τα περιεχόμενα
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));
    
    // Καθαρίζει την ενεργή τάξη από όλα τα tabs
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Ενεργοποιεί το επιλεγμένο tab και περιεχόμενο
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}


    // Εναλλαγή ενεργής κατάστασης
function changeStatus(statusId) {
    // Απόκρυψη όλων των sections
    const sections = document.querySelectorAll('.status-section');
    sections.forEach(section => section.classList.remove('active'));

    // Εμφάνιση του αντίστοιχου section
    const activeSection = document.getElementById(statusId);
    if (activeSection) {
        activeSection.classList.add('active');
    }
}

// Προσθήκη μέλους επιτροπής
function addMember() {
    const input = document.getElementById('professor');
    const list = document.getElementById('committee-list');

    if (input.value.trim() !== '') {
        const listItem = document.createElement('li');
        listItem.textContent = input.value;
        list.appendChild(listItem);
        input.value = ''; // Καθαρισμός πεδίου
    } else {
        alert('Παρακαλώ εισάγετε όνομα διδάσκοντα.');
    }
}

/*
// Ολοκλήρωση ανάθεσης
function finalizeAssignment() {
    alert('Η ανάθεση της διπλωματικής ολοκληρώθηκε.');
}

// Υποβολή αρχείου και λεπτομερειών εξέτασης
document.getElementById('upload-draft-form')?.addEventListener('submit', function (event) {
    event.preventDefault();
    alert('Το πρόχειρο και οι πληροφορίες εξέτασης καταχωρήθηκαν επιτυχώς.');
});

// Δημοσίευση ανακοίνωσης παρουσίασης
function publishPresentation() {
    alert('Η ανακοίνωση για την παρουσίαση δημιουργήθηκε.');
}

// Καταχώρηση βαθμού
function submitGrade() {
    alert('Ο βαθμός της διπλωματικής καταχωρήθηκε.');
}
*/
/*
////////////////////τα στεπσ αλλαζουν αυτοματα malloon!!!!! an then leiotyrgei apo kato einai to palio
document.addEventListener("DOMContentLoaded", () => {
    const stepMap = {
        "under_assignment": 1,
        "active": 2,
        "under_review": 3,
        "completed": 4
    };

    function updateStepper(currentStep) {
        const totalSteps = 4;

        // Ενημέρωση του κάθε βήματος
        for (let i = 1; i <= totalSteps; i++) {
            const step = document.getElementById(`step-${i}`);
            const content = document.getElementById(`content-${i}`);

            if (i < currentStep) {
                step.classList.remove("active");
                step.classList.add("completed");
                content.classList.remove("active");
            } else if (i === currentStep) {
                step.classList.add("active");
                step.classList.remove("completed");
                content.classList.add("active");
            } else {
                step.classList.remove("active", "completed");
                content.classList.remove("active");
            }
        }
    }

    function checkThesisStatus() {
        fetch("http://localhost/web-project/get_thesis_status.php")
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const currentStatus = data.status;
                    const currentStep = stepMap[currentStatus];
                    updateStepper(currentStep);
                } else {
                    console.error("Error:", data.message);
                }
            })
            .catch(error => console.error("Error fetching thesis status:", error));
    }

    // Εκκίνηση ελέγχου κατάστασης κάθε 5 δευτερόλεπτα
    setInterval(checkThesisStatus, 5000);

    // Πρώτος έλεγχος κατά τη φόρτωση
    checkThesisStatus();
});

*/
// JavaScript for stepper interaction with animation
/*
let currentStep = 1; // Start at step 1
const totalSteps = 4; // Total number of steps

 // Button element
 const nextBtn = document.getElementById('next-btn');
      
 nextBtn.addEventListener('click', () => {
    if (currentStep < totalSteps) {
        // Mark current step as completed
        document.getElementById(`step-${currentStep}`).classList.remove('active');
        document.getElementById(`step-${currentStep}`).classList.add('completed');
        document.getElementById(`line-${currentStep}`).classList.add('completed');
        document.getElementById(`content-${currentStep}`).classList.remove('active');

        // Move to the next step
        currentStep++;
        document.getElementById(`step-${currentStep}`).classList.add('active');
        document.getElementById(`content-${currentStep}`).classList.add('active');

        // Disable button if the last step is reached
        if (currentStep === totalSteps) {
            nextBtn.disabled = true;
        }
    }
});*/

document.addEventListener("DOMContentLoaded", () => {
    // Παράδειγμα φόρτωσης δεδομένων (όπως το είχαμε προηγουμένως)
    fetch("http://localhost/web-project/pages/profile.php") // Αρχείο PHP για ανάκτηση δεδομένων
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            document.getElementById("name").value = data.name || "";
            document.getElementById("surname").value = data.surname || "";
            document.getElementById("student_number").value = data.student_number || "";
            document.getElementById("father_name").value = data.father_name || "";
            document.getElementById("city").value = data.city || "";
            document.getElementById("street").value = data.street || "";
            document.getElementById("number").value = data.number || "";
            document.getElementById("postcode").value = data.postcode || "";
            document.getElementById("mobilePhone").value = data.mobile_telephone || "";
            document.getElementById("phone").value = data.landline_telephone || "";
            document.getElementById("email").value = data.email || "";
        });
    
    // Λειτουργία αποθήκευσης κατά το πάτημα του κουμπιού "Αποθήκευση"
    document.getElementById("profileForm").addEventListener("submit", event => {
        event.preventDefault();  // Αποτροπή της προεπιλεγμένης συμπεριφοράς του form

        // Συλλογή δεδομένων από τα πεδία της φόρμας
        const data = {
            street: document.getElementById("street").value,
            number: document.getElementById("number").value,
            postcode: document.getElementById("postcode").value,
            city: document.getElementById("city").value,
            mobile_telephone: document.getElementById("mobilePhone").value,
            landline_telephone: document.getElementById("phone").value,
            email: document.getElementById("email").value,
        };

        // Αποστολή των δεδομένων στο PHP αρχείο για αποθήκευση
        fetch("http://localhost/web-project/pages/update_profile.php", { // Αρχείο PHP για ενημέρωση δεδομένων
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(result => {
            // Δημιουργία και εμφάνιση μηνύματος
            const messageContainer = document.createElement("div");
            messageContainer.style.textAlign = "center";
            messageContainer.style.marginTop = "10px";

            // Αν η αποθήκευση ήταν επιτυχής
            if (result.success) {
                messageContainer.style.color = "green";
                messageContainer.innerHTML = "<p>Οι αλλαγές αποθηκεύτηκαν με επιτυχία!</p>";
                console.log("Οι αλλαγές αποθηκεύτηκαν με επιτυχία!");
            } else {
                // Αν υπήρξε σφάλμα
                messageContainer.style.color = "red";
                messageContainer.innerHTML = "<p>Σφάλμα κατά την αποθήκευση. Παρακαλώ προσπαθήστε ξανά.</p>";
                console.log("Σφάλμα κατά την αποθήκευση.");
            }

            // Προσθήκη του μηνύματος στο DOM
            document.body.appendChild(messageContainer); // ή χρησιμοποιήστε το container της φόρμας σας
        })
        .catch(error => {
            // Εμφάνιση σφάλματος αν υπάρχει πρόβλημα με την αίτηση
            const messageContainer = document.createElement("div");
            messageContainer.style.textAlign = "center";
            messageContainer.style.marginTop = "10px";
            messageContainer.style.color = "red";
            messageContainer.innerHTML = "<p>Σφάλμα κατά την αποθήκευση. Παρακαλώ προσπαθήστε ξανά.</p>";
            document.body.appendChild(messageContainer);
            console.log("Σφάλμα κατά την αποθήκευση.");
        });
    });
});

///////////////εμφάνιση δεδομένων στον πίνακα!!!!

document.addEventListener("DOMContentLoaded", function () {
    function loadTheses(status) {
        fetch(`http://localhost/web-project/pages/fetch_theses_students.php?status=${status}`)
            .then(response => response.json())
            .then(data => {
                console.log(`Response Data for status ${status}:`, data);

                const tableBody = document.getElementById("thesisTableBody");

                if (!data.success || !data.data || data.data.length === 0) {
                    console.warn(`Δεν βρέθηκαν δεδομένα για την κατάσταση: ${status}`);
                    return; // Συνεχίζουμε χωρίς να αντικαταστήσουμε τον πίνακα
                }

                data.data.forEach(thesis => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${thesis.title || 'Χωρίς Τίτλο'}</td>
                        <td>${thesis.summary || 'Χωρίς Περιγραφή'}</td>
                        <td>${thesis.pdf_file ? `<a href="${thesis.pdf_file}" target="_blank">Download</a>` : 'N/A'}</td>
                        <td>${thesis.status || 'N/A'}</td>
                        <td>${thesis.committee || 'N/A'}</td>
                        <td>${thesis.duration || 'N/A'}</td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error(`Error fetching data for status ${status}:`, error);
            });
    }

    // Αρχική εκκαθάριση πίνακα
    const tableBody = document.getElementById("thesisTableBody");
    tableBody.innerHTML = ''; // Καθαρισμός πριν τη φόρτωση δεδομένων

    // Φόρτωση δεδομένων για κάθε κατάσταση
    loadTheses("temporary"); // Προσωρινά
    loadTheses("under_assignment"); // Υπό ανάθεση
    loadTheses("active"); // Ενεργές
    loadTheses("under_review");
});





////////////////////////////////////////////////////////////////// step 1 apostoli aitimatow gia comiteee
const committeeMembers = []; // Λίστα διδασκόντων που προστέθηκαν

// Λειτουργία για προσθήκη διδάσκοντα στη λίστα
function addMember() {
    const professorInput = document.getElementById('professor');
    const professorName = professorInput.value.trim();

    if (professorName === '') {
        alert('Παρακαλώ εισάγετε όνομα Διδάσκοντα.');
        return;
    }

    // Διαχωρισμός όνομα και επώνυμο
    const [name, surname] = professorName.split(' ');

    // Επικύρωση
    if (!name || !surname) {
        alert('Παρακαλώ εισάγετε σωστά το όνομα και το επώνυμο του διδάσκοντα.');
        return;
    }

    // Προσθήκη στη λίστα
    committeeMembers.push({ name, surname });

    // Ενημέρωση UI
    const list = document.getElementById('committee-list');
    const listItem = document.createElement('li');
    listItem.textContent = professorName;
    list.appendChild(listItem);

    // Καθαρισμός πεδίου εισαγωγής
    professorInput.value = '';
}

// Λειτουργία για αποστολή δεδομένων και ολοκλήρωση της αναθέσης
function finalizeCommittee() {
    if (committeeMembers.length < 2) {
        alert('Η επιτροπή πρέπει να έχει τουλάχιστον 2 μέλη.');
        return;
    }

    console.log('Δεδομένα προς αποστολή:', { professors: committeeMembers }); // Debug

    fetch('http://localhost/web-project/pages/create_commitee.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ professors: committeeMembers }) // Δεδομένα προς αποστολή
    })

        .then(response => response.json())
        .then(result => {
            console.log('Απάντηση από το API:', result); // Debug
            if (result.success) {
                alert('Η επιτροπή δημιουργήθηκε και οι προσκλήσεις στάλθηκαν στους διδάσκοντες.');
                document.getElementById('committee-list').innerHTML = '';
                committeeMembers.length = 0;
            } else {
                alert('Σφάλμα κατά τη δημιουργία της επιτροπής: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά την αποστολή του αιτήματος:', error);
            alert('Προέκυψε πρόβλημα κατά την αποστολή του αιτήματος.');
        });
}
        
/*

///////////////////////steob 3 kai 4
/////////////////////////////////// ανεβασμα πδφ σε πίνακα 
    async function loadTheses() {
        const response = await fetch('fetch_theses.php');
        const data = await response.json();
        const tableBody = document.getElementById('thesisTableBody');
        tableBody.innerHTML = ''};

async function uploadDraft() {
    const formData = new FormData(document.getElementById('upload-form'));
    const response = await fetch('upload_draft.php', {
        method: 'POST',
        body: formData,
    })};
    async function loadTheses() {
        // Αίτημα στο backend για δεδομένα
        const response = await fetch('fetch_theses.php');
        const data = await response.json();

        // Επιλογή του πίνακα
        const tableBody = document.getElementById('thesisTableBody');
        tableBody.innerHTML = ''; // Καθαρισμός παλαιών γραμμών

        data.forEach(thesis => {
            const row = document.createElement('tr');

            // Δημιουργία κελιών
            row.innerHTML = `
                <td>${thesis.title}</td>
                <td>${thesis.summary}</td>
                <td><a href="${thesis.pdf}" target="_blank">Λήψη</a></td>
                <td>${thesis.status}</td>
                <td>${thesis.committee}</td>
                <td>${thesis.duration}</td>
            `;

            // Προσθήκη γραμμής στον πίνακα
            tableBody.appendChild(row);
        });
    }

    // Κλήση της συνάρτησης για φόρτωση δεδομένων
    loadTheses();
    
    async function scheduleExam() {
        const formData = new FormData(document.getElementById('exam-form'));
        const response = await fetch('schedule_exam.php', {
            method: 'POST',
            body: formData,
        });
        const result = await response.json();
        alert(result.message);
    }

    function toggleFields() {
        const examMode = document.getElementById("exam_mode").value;
        const roomRow = document.getElementById("room_row");
        const onlineRoomRow = document.getElementById("online_room_row");

        if (examMode === "in-person") {
            roomRow.style.display = "table-row"; // Εμφάνιση γραμμής για αίθουσα
            onlineRoomRow.style.display = "none"; // Απόκρυψη γραμμής για διαδικτυακή αίθουσα
        } else if (examMode === "online") {
            onlineRoomRow.style.display = "table-row"; // Εμφάνιση γραμμής για διαδικτυακή αίθουσα
            roomRow.style.display = "none"; // Απόκρυψη γραμμής για αίθουσα
        } else {
            roomRow.style.display = "none"; // Απόκρυψη και των δύο γραμμών αν δεν έχει επιλεγεί κάτι
            onlineRoomRow.style.display = "none";
        }
    }
    function setRepositoryLink() {
        // Λήψη της τιμής του πεδίου εισαγωγής
        const linkInput = document.getElementById("repository_link_input").value;
        const linkDisplay = document.getElementById("repository_link_display");
        const linkElement = document.getElementById("repository_link");

        if (linkInput.trim() === "") {
            alert("Παρακαλώ εισάγετε έναν έγκυρο σύνδεσμο.");
            return;
        }

        // Ενημέρωση του συνδέσμου
        linkElement.href = linkInput;
        linkDisplay.style.display = "block"; // Εμφάνιση της γραμμής με τον σύνδεσμο
    }
    */