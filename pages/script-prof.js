

document.querySelector('.menu-toggle').addEventListener('click', function() {
    this.classList.toggle('active'); // Rotate arrow
    document.querySelector('.navbar ul').classList.toggle('active'); // Show/hide menu
});




function openTab(event, tabName) {
    var i, tabContent, tabs;
    tabContent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabContent.length; i++) {
        tabContent[i].classList.remove("active");
    }
    tabs = document.getElementsByClassName("tab");
    for (i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove("active");
    }
    document.getElementById(tabName).classList.add("active");
    event.currentTarget.classList.add("active");
}
// Άνοιγμα φόρμας όταν πατηθεί το κουμπί +
document.getElementById('create-topic-btn').addEventListener('click', function() {
    document.getElementById('create-topic-form').style.display = 'block';
});

// Κλείσιμο φόρμας όταν πατηθεί το X
document.getElementById('close-form').addEventListener('click', function() {
    document.getElementById('create-topic-form').style.display = 'none';
});
function openModal() {
    document.getElementById("modal").style.display = "block";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

// Close the modal if the user clicks outside of the modal content
window.onclick = function(event) {
    if (event.target == document.getElementById("modal")) {
        closeModal();
    }
}
function openTab(evt, tabName) {
    var i, tabContent, tabLinks;

    // Απόκρυψη όλων των περιεχομένων των tabs
    tabContent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabContent.length; i++) {
        tabContent[i].style.display = "none";
    }

    // Αφαίρεση της ενεργής κλάσης από όλα τα tabs
    tabLinks = document.getElementsByClassName("tab");
    for (i = 0; i < tabLinks.length; i++) {
        tabLinks[i].className = tabLinks[i].className.replace(" active", "");
    }

    // Εμφάνιση του επιλεγμένου περιεχομένου του tab και προσθήκη της ενεργής κλάσης
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

// Αρχική προβολή του πρώτου tab
document.getElementsByClassName("tab")[0].click();

document.addEventListener('DOMContentLoaded', function () {
    // Φόρτωση των θεμάτων από το backend
    fetch("http://localhost/web-project/pages/view_theses.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const thesisTableBody = document.getElementById('thesisTableBody');
                thesisTableBody.innerHTML = ''; 

                data.theses.forEach(thesis => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${thesis.title}</td>
                        <td>${thesis.description}</td>
                        <td><a href="${thesis.pdf_file}" target="_blank">Αρχείο PDF</a></td>
                        <td><button class="editButton" data-id="${thesis.id}">Επεξεργασία</button></td>
                    `;
                    thesisTableBody.appendChild(row);
                });
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error("Σφάλμα:", error);
            alert("Προέκυψε σφάλμα κατά τη φόρτωση των θεμάτων.");
        });

    // Χρήση event delegation για τα κουμπιά "Επεξεργασία"
    document.getElementById('thesisTableBody').addEventListener('click', function (e) {
        if (e.target.classList.contains('editButton')) {
            const thesisId = e.target.getAttribute('data-id');
            fetch(`http://localhost/web-project/pages/get_thesis.php?id=${thesisId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('thesisTitle').value = data.title;
                        document.getElementById('thesisDescription').value = data.description;
                        document.getElementById('thesisPdf').value = data.pdf_file || '';
                        document.getElementById('thesisId').value = thesisId;

                        document.getElementById('editForm').style.display = 'block';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error("Σφάλμα:", error);
                    alert("Προέκυψε σφάλμα κατά την φόρτωση των δεδομένων.");
                });
        }
    });
    // Προσθήκη event listener για το κουμπί Ακύρωσης
    document.getElementById('cancelChanges').addEventListener('click', function () {
        // Κλείσιμο της φόρμας χωρίς αποθήκευση αλλαγών
        document.getElementById('editForm').style.display = 'none';
    });

    // Αποθήκευση των αλλαγών στη βάση δεδομένων
    document.getElementById('saveChanges').addEventListener('click', function () {
        const thesisId = document.getElementById('thesisId').value;
        const thesisTitle = document.getElementById('thesisTitle').value;
        const thesisDescription = document.getElementById('thesisDescription').value;
        const thesisPdf = document.getElementById('thesisPdf').value;

        fetch("http://localhost/web-project/pages/update_thesis.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id: thesisId,
                title: thesisTitle,
                description: thesisDescription,
                pdf_file: thesisPdf
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Οι αλλαγές αποθηκεύτηκαν επιτυχώς.");
                document.getElementById('editForm').style.display = 'none';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error("Σφάλμα:", error);
            alert("Προέκυψε σφάλμα κατά την αποθήκευση των αλλαγών.");
        });
    });
});

function submitForm() {
    const form = document.getElementById('create-topic-form1');
    const formData = new FormData(form);

    fetch('http://localhost/web-project/pages/thesis_topics.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())  // Παίρνουμε το κείμενο που επιστρέφει ο server
    .then(data => {
        // Εμφανίζουμε το μήνυμα επιτυχίας ή αποτυχίας
        document.getElementById('success-notification').innerHTML = data;
        document.getElementById('success-notification').style.display = 'block';  // Εμφάνιση μηνύματος επιτυχίας
        
        // Περιμένουμε 2 δευτερόλεπτα πριν κλείσουμε το παράθυρο
        setTimeout(() => {
            document.getElementById('create-topic-form1').style.display = 'none';  // Κλείσιμο παραθύρου
            document.getElementById('success-notification').style.display = 'none';  // Απόκρυψη του μηνύματος επιτυχίας
        }, 2000000);  // 2 δευτερόλεπτα καθυστέρηση
    })
    .catch(error => {
        console.error('Σφάλμα:', error);
        document.getElementById('success-notification').innerHTML = "Σφάλμα κατά την αποστολή του αιτήματος.";
        document.getElementById('success-notification').style.display = 'block';  // Εμφάνιση σφάλματος
    });
}
 // Αρχικοποίηση γραφημάτων με Chart.js
 function createCharts() {
    // Στατιστικά για τις διπλωματικές που έχει επιβλέψει
    var supervisionData = {
        labels: ['Μέσος Χρόνος', 'Μέσος Βαθμός', 'Συνολικό Πλήθος'],
        datasets: [{
            label: 'Στατιστικά Επιβλέψεων',
            data: [8.5, 7.2, 15], // Τιμές για Μέσο Χρόνο, Μέσο Βαθμό, Συνολικό Πλήθος
            backgroundColor: 'rgba(0, 123, 255, 0.5)',
            borderColor: 'rgba(0, 123, 255, 1)',
            borderWidth: 1
        }]
    };

    var committeeData = {
        labels: ['Μέσος Χρόνος', 'Μέσος Βαθμός', 'Συνολικό Πλήθος'],
        datasets: [{
            label: 'Στατιστικά Τριμελούς Επιτροπής',
            data: [9.1, 6.8, 20], // Τιμές για Μέσο Χρόνο, Μέσο Βαθμό, Συνολικό Πλήθος
            backgroundColor: 'rgba(40, 167, 69, 0.5)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }]
    };

    // Δημιουργία του γραφήματος για τις επιβλέψεις
    var ctx1 = document.getElementById('supervision-chart').getContext('2d');
    var supervisionChart = new Chart(ctx1, {
        type: 'bar', // Τύπος γραφήματος (μπαρ)
        data: supervisionData
    });

    // Δημιουργία του γραφήματος για το μέλος της τριμελούς
    var ctx2 = document.getElementById('committee-chart').getContext('2d');
    var committeeChart = new Chart(ctx2, {
        type: 'bar', // Τύπος γραφήματος (μπαρ)
        data: committeeData
    });
}

// Κλήση της συνάρτησης για τη δημιουργία των γραφημάτων μόλις φορτωθεί η σελίδα
window.onload = function() {
    createCharts();
};

/*
function changeStatus(status) {
    // Απόκρυψη όλων των περιοχών
    const sections = document.querySelectorAll('.status-section');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    // Εμφάνιση της περιοχής για την τρέχουσα κατάσταση
    const activeSection = document.getElementById(status);
    activeSection.style.display = 'block';
}

function cancelAssignment() {
    // Λογική για την ακύρωση της ανάθεσης
    alert("Η ανάθεση του θέματος έχει ακυρωθεί.");
}

function changeStatusToReview() {
    // Λογική για την αλλαγή κατάστασης σε "Υπό Εξέταση"
    alert("Η κατάσταση της διπλωματικής άλλαξε σε 'Υπό Εξέταση'.");
}

function cancelActiveAssignment() {
    // Λογική για την ακύρωση της ανάθεσης (με παρέλευση 2 ετών)
    alert("Η ανάθεση διπλωματικής έχει ακυρωθεί.");
}

function publishPresentation() {
    // Λογική για την δημιουργία και προβολή της ανακοίνωσης παρουσίασης
    alert("Η ανακοίνωση για την παρουσίαση έχει δημιουργηθεί.");
}

function submitGrade() {
    // Λογική για την καταχώρηση βαθμού
    alert("Ο βαθμός για τη διπλωματική έχει καταχωρηθεί.");
}

// Ρυθμίσεις για την εμφάνιση της πρώτης κατάστασης κατά την αρχική φόρτωση
window.onload = function() {
    changeStatus('under-assignment');
};
*/

fetch('http://localhost/web-project/pages/view_theses.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Σφάλμα κατά την ανάκτηση των δεδομένων');
        }
        return response.text();
    })
    .then(data => {
        if (data.success) {
            // Αν υπάρχουν θέματα, τα εμφανίζουμε
            let tableContent = '';
            data.theses.forEach(thesis => {
                tableContent += `
                    <tr>
                        <td>${thesis.title}</td>
                        <td>${thesis.description}</td>
                        <td><a href="${thesis.pdf_file}" target="_blank">Αρχείο PDF</a></td>
                    </tr>
                `;
            });
            document.getElementById('thesisTableBody').innerHTML = tableContent;
        }
    })

    .catch(error => {
        console.error('Σφάλμα:', error);
        document.getElementById('thesisTableBody').innerHTML = 
            '<tr><td colspan="3">Σφάλμα κατά την ανάκτηση των δεδομένων</td></tr>';
    });

    function searchStudent() {
        const studentId = document.getElementById("student-id").value;
        const messageDiv = document.getElementById("message"); // Το div όπου θα εμφανιστεί το μήνυμα
    
        fetch(`http://localhost/web-project/pages/assignment/search_student.php?studentId=${encodeURIComponent(studentId)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Εμφανίζει το μήνυμα ότι ο φοιτητής βρέθηκε
                    messageDiv.textContent = "Ο φοιτητής βρέθηκε επιτυχώς!";
                    messageDiv.style.color = "green";
                    
                    // Εάν θέλεις να εμφανίσεις και άλλες πληροφορίες του φοιτητή:
                    console.log("Student found:", data.student); 
                    document.getElementById("student-id").value = data.student.student_number; 
                } else {
                    // Εμφανίζει μήνυμα ότι ο φοιτητής δεν βρέθηκε
                    messageDiv.textContent = "Ο φοιτητής δεν βρέθηκε.";
                    messageDiv.style.color = "red";
                }
            })
            .catch(error => {
                console.error("Error:", error);
                messageDiv.textContent = "Προέκυψε σφάλμα κατά την αναζήτηση.";
                messageDiv.style.color = "red";
            });
    }
    document.addEventListener("DOMContentLoaded", () => {
        fetchTopics();
    });
    
    function fetchTopics() {
        fetch("http://localhost/web-project/pages/view_thesis.php") // Το endpoint για ανάκτηση θεμάτων
            .then(response => {
                if (!response.ok) {
                    throw new Error("Failed to fetch topics");
                }
                return response.json();
            })
            .then(data => {
                const topicList = document.getElementById("topic-list");
                const assignmentMessage = document.getElementById("assignment-message");
    
                if (data.success) {
                    const theses = data.theses; // Απόκτησε τη λίστα θεμάτων
                    if (theses.length > 0) {
                        // Αν υπάρχουν θέματα, τα προσθέτουμε στον πίνακα επιλογών
                        theses.forEach(topic => {
                            const option = document.createElement("option");
                            option.value = topic.id; // ID θέματος
                            option.textContent = topic.title; // Τίτλος θέματος
                            topicList.appendChild(option);
                        });
                    } else {
                        // Αν δεν υπάρχουν θέματα, εμφανίζουμε το μήνυμα στον πίνακα
                        assignmentMessage.textContent = "Δεν υπάρχουν θέματα για αυτόν τον καθηγητή.";
                    }
                } else {
                    console.error("No topics found:", data.message);
                    assignmentMessage.textContent = data.message;
                }
            })
            .catch(error => {
                console.error("Error fetching topics:", error);
                document.getElementById("assignment-message").textContent = 
                    "Αποτυχία φόρτωσης λίστας θεμάτων.";
            });
    }
    
    function assignTopic() {
        const studentId = document.getElementById("student-id").value;
        const topicId = document.getElementById("topic-list").value;
        const messageDiv = document.getElementById("assignment-message");
    
        console.log("Student ID:", studentId); // Εμφανίζει στην κονσόλα για έλεγχο
        console.log("Topic ID:", topicId); // Εμφανίζει στην κονσόλα για έλεγχο
    
        // Ελέγχουμε αν υπάρχουν τα απαιτούμενα πεδία
        if (!studentId || !topicId) {
            messageDiv.textContent = "Παρακαλώ συμπληρώστε και τα δύο πεδία.";
            messageDiv.style.color = "red";
            return;
        }
    
        // Αποστολή του αιτήματος στο API για την ανάθεση του θέματος
        fetch("http://localhost/web-project/pages/assignment/assign_topic.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
             studentId: studentId,
            topic: topicId  // Στείλουμε το σωστό πεδίο 'topic'
            })
    
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Διαχείριση επιτυχούς ανάθεσης
                console.log(data.message);
                messageDiv.textContent = "Η ανάθεση θέματος ολοκληρώθηκε με επιτυχία!";
                messageDiv.style.color = "green";
            } else {
                // Διαχείριση αποτυχίας ανάθεσης
                console.error(data.message);
                messageDiv.textContent = "Η ανάθεση θέματος απέτυχε. Προσπαθήστε ξανά.";
                messageDiv.style.color = "red";
            }
        })
        .catch(error => {
            // Διαχείριση σφαλμάτων δικτύου ή άλλων προβλημάτων
            console.error('Error:', error);
            messageDiv.textContent = "Αποτυχία ανάθεσης θέματος.";
            messageDiv.style.color = "red";
        });
    }
    function cancelAssignment() {
        const studentId = document.getElementById("student-id").value;
        const messageDiv = document.getElementById("cancel-message");
    
        if (!studentId) {
            messageDiv.textContent = "Πρέπει πρώτα να αναζητήσετε ένα φοιτητή.";
            messageDiv.style.color = "red";
            return;
        }
    
        fetch("http://localhost/web-project/pages/assignment/cancel_assignment.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.textContent = "Η ανάθεση του θέματος ακυρώθηκε.";
                messageDiv.style.color = "green";
            } else {
                messageDiv.textContent = data.message;
                messageDiv.style.color = "red";
            }
        })
        .catch(error => {
            console.error("Error:", error);
            messageDiv.textContent = "Προέκυψε σφάλμα κατά την ακύρωση.";
            messageDiv.style.color = "red";
        });
    }



    function applyFilters() {
    // Λήψη των επιλεγμένων τιμών από τα φίλτρα
    var status = document.getElementById("status").value;
    var role = document.getElementById("role").value;

    // Δημιουργία του φίλτρου για το αίτημα
    var filters = { status: status, role: role };

    // Χρήση Fetch API για να στείλουμε το αίτημα στον server
    loadTheses(filters); // Χρήση της loadTheses για να φορτώσουμε τα δεδομένα
}

    
function loadTheses(filters = {}) {
    const url = new URL('http://localhost/web-project/pages/fetch_theses.php', window.location.origin);
    
    // Προσθήκη των φίλτρων στα παραμέτρους της URL
    Object.keys(filters).forEach(key => url.searchParams.append(key, filters[key]));

    fetch(url)

        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text(); // Λήψη της απόκρισης ως κείμενο πρώτα
        })
        .then(text => {
            try {
                const data = JSON.parse(text); // Προσπάθεια να μετατραπεί το κείμενο σε JSON
                renderThesisList(data); // Αν πετύχει, προβάλλουμε τα δεδομένα
            } catch (error) {
                console.error('Error parsing JSON:', error); // Αν δεν είναι έγκυρο JSON, εμφανίζουμε σφάλμα
                console.error('Response Text:', text); // Εμφανίζουμε την ακατέργαστη απόκριση για να καταλάβουμε τι έγινε
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}



function renderThesisList(data) {
    const tableBody = document.querySelector("#thesisList tbody");
    tableBody.innerHTML = ""; // Καθαρισμός παλιών δεδομένων

    data.forEach(thesis => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${thesis.thesis_title}</td>
            <td>${thesis.student_name} ${thesis.student_surname}</td>
            <td>${thesis.thesis_status}</td>
            <td>${thesis.professor_name} ${thesis.professor_surname}</td>
            <td>${thesis.topic_title}</td>
        `;
        tableBody.appendChild(row);
    });
}

window.fetchTheses = function fetchTheses() {
    const status_filter = document.getElementById('status_filter').value;
    const role_filter = document.getElementById('role_filter').value;

    const container = document.getElementById('theses-list');
    container.innerHTML = '<p>Φόρτωση δεδομένων...</p>';

    fetch(`http://localhost/web-project/pages/fetch_theses_prof.php?status=${status_filter}&role=${role_filter}`)
        .then(response => response.json())
        .then(data => {
            container.innerHTML = ''; // Καθαρισμός προηγούμενων δεδομένων

            if (data.success && data.data && data.data.length > 0) {
                const table = document.createElement('table');
                table.className = 'theses-table';
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Θέμα</th>
                            <th>Περιγραφή</th>
                            <th>Φοιτητής</th>
                            <th>Κατάσταση</th>
                            <th>Τριμελής</th>
                            <th>Ημερομηνία Δημιουργίας</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;

                const tbody = table.querySelector('tbody');

                data.data.forEach(thesis => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${thesis.thesis_title || 'Χωρίς Τίτλο'}</td>
                        <td>${thesis.thesis_description || 'Χωρίς Περιγραφή'}</td>
                        <td>${thesis.student_name || 'Χωρίς Φοιτητή'}</td>
                        <td>${thesis.thesis_status || 'Άγνωστη'}</td>
                        <td>${thesis.committee_members || 'Χωρίς Επιτροπή'}</td>
                        <td>${thesis.thesis_created_date || 'Άγνωστη'}</td>
                    `;
                    tbody.appendChild(row);
                });

                container.appendChild(table);
            } else {
                container.innerHTML = '<p>Δεν υπάρχουν διαθέσιμες διπλωματικές για το φίλτρο σας.</p>';
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά τη λήψη δεδομένων:', error);
            container.innerHTML = '<p>Σφάλμα κατά τη φόρτωση δεδομένων.</p>';
        });
};



function exportData(format) {
    if (!['csv', 'json'].includes(format)) {
        alert('Μη έγκυρη μορφή εξαγωγής.');
        return;
    }
    window.location.href = `http://localhost/web-project/pages/fetch_theses_prof.php?export=${format}`;
}


    
    

    fetch('http://localhost/web-project/pages/load_invitations.php')
    .then(function(response) {
        return response.json();
    })
    .then(function(committeeRequests) {
        console.log('Απόκριση:', committeeRequests);
        if (committeeRequests.success) {
            const committeeRequestsList = document.getElementById('committee-requests-list');
            committeeRequestsList.innerHTML = '';

            committeeRequests.data.forEach(function(request) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <th>${request.thesis_title}</th>
                    <th>${request.student_surname}</th>
                    <th>${request.student_am}</th>
                    <td>
                        <button onclick="acceptRequest(${request.id})">Αποδοχή</button>
                        <button onclick="rejectRequest(${request.id})">Απόρριψη</button>
                    </td>
                `;
                committeeRequestsList.appendChild(row);
            });
        } else {
            alert(committeeRequests.message || 'Δεν βρέθηκαν αιτήσεις επιτροπών.');
        }
    })
    .catch(function(error) {
        console.error('Σφάλμα κατά τη φόρτωση αιτήσεων επιτροπών:', error);
        alert('Προέκυψε πρόβλημα κατά τη φόρτωση των αιτήσεων.');
    });



    
    function acceptRequest(invitationId) {
        fetch('http://localhost/web-project/pages/manage_invitation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: invitationId, action: 'accept' })
        })
        .then(response => response.json())  // Ανάλυση του JSON που επιστρέφει το PHP script
        .then(result => {
            if (result.success) {
                alert('Η πρόσκληση αποδεχθήκε επιτυχώς.');
                  // Ανανεώνει τη λίστα των προσκλήσεων
                
            } else {
                alert('Σφάλμα κατά την αποδοχή της πρόσκλησης: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά την αποδοχή της πρόσκλησης:', error);
            alert('Προέκυψε πρόβλημα κατά την αποδοχή της πρόσκλησης.');
        });
    }
    
    function rejectRequest(invitationId) {
        fetch('http://localhost/web-project/pages/manage_invitation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: invitationId, action: 'reject' })
        })
        .then(response => response.json())  // Ανάλυση του JSON που επιστρέφει το PHP script
        .then(result => {
            if (result.success) {
                alert('Η πρόσκληση απορρίφθηκε επιτυχώς.');
                  // Ανανεώνει τη λίστα των προσκλήσεων
                
            } else {
                alert('Σφάλμα κατά την απόρριψη της πρόσκλησης: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά την απόρριψη της πρόσκλησης:', error);
            alert('Προέκυψε πρόβλημα κατά την απόρριψη της πρόσκλησης.');
        });
    }
    function filterTheses(status) {
        const container = document.getElementById('thesis-container');
        container.innerHTML = 'Φόρτωση δεδομένων...';
    
        fetch(`http://localhost/web-project/pages/get_status.php?status=${status}`)
            .then(response => response.json())
            .then(data => {
                console.log('Απαντήσεις API:', data);
                container.innerHTML = ''; // Καθαρίζει το container
    
                if (data.success && data.theses) {
                    data.theses.forEach(thesis => {
                        const thesisDiv = document.createElement('div');
                        thesisDiv.className = 'thesis-item';
    
                        // Προσθέτει το περιεχόμενο της κάρτας
                        thesisDiv.innerHTML = `
                            <h3>${thesis.title || 'Χωρίς Τίτλο'}</h3>
                            <p>${thesis.description || 'Χωρίς Περιγραφή'}</p>
                            <p>Φοιτητής: ${thesis.student_name || 'Χωρίς Φοιτητή'} ${thesis.student_surname || ''}</p>
                        `;
    
                        // Εμφάνιση κουμπιών ανάλογα με την κατάσταση
                        if (status === 'under_assignment') {
                            thesisDiv.innerHTML += `
                                <button onclick="viewThesis(${thesis.id})">Προβολή Λεπτομερειών</button>
                                <button class="cancel-button" onclick="cancelAssignment(${thesis.id})">Ακύρωση Ανάθεσης</button>
                            `;
                        } else if (status === 'active') {
                            thesisDiv.innerHTML += `
                                <button onclick="addNote(${thesis.id})">Προσθήκη Σημείωσης</button>
                                <button onclick="cancelAssignmentWithReason(${thesis.id})">Ακύρωση Ανάθεσης</button>
                                <button onclick="changeStatusToUnderReview(${thesis.id})">Μετάβαση σε Υπό Εξέταση</button>
                            `;
                        } else if (status === 'under_review') {
                            thesisDiv.innerHTML += `
                                <button onclick="viewDraft(${thesis.id})">Προβολή Πρόχειρου Κειμένου</button>
                                <button onclick="generateAnnouncement(${thesis.id})">Παραγωγή Ανακοίνωσης</button>
                                <button onclick="addGrade(${thesis.id})">Καταχώρηση Βαθμού</button>
                                <button onclick="viewGrades(${thesis.id})">Προβολή Βαθμολογιών</button>
                            `;
                        }
    
                        // Προσθήκη της κάρτας στο container
                        container.appendChild(thesisDiv);
                    });
                } else {
                    container.innerHTML = '<p>Δεν υπάρχουν διπλωματικές σε αυτή την κατάσταση.</p>';
                }
            })
            .catch(error => {
                console.error('Σφάλμα φόρτωσης:', error);
                container.innerHTML = '<p>Προέκυψε σφάλμα κατά τη φόρτωση.</p>';
            });
    }
    
      
    function viewThesis(id) {
        console.log('Προβολή λεπτομερειών για ID:', id);
    
        const modalContent = document.getElementById('modal-content');
        modalContent.innerHTML = '<p>Φόρτωση δεδομένων...</p>'; // Placeholder
    
        fetch(`http://localhost/web-project/pages/get_committee_members.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                if (!text) {
                    throw new Error('Empty response from server');
                }
                return JSON.parse(text);
            })
            .then(data => {
                console.log('Δεδομένα API:', data);
    
                modalContent.innerHTML = ''; // Καθαρισμός περιεχομένου
    
                if (data.success && data.members && data.members.length > 0) {
                    data.members.forEach(member => {
                        modalContent.innerHTML += `
                            <strong>${member.professor_name} ${member.professor_surname}</strong><br>
                            <span>Τίτλος Διπλωματικής: ${member.thesis_title || 'N/A'}</span><br>
                            <span>Ημερομηνία Πρόσκλησης: ${member.invitation_date || 'N/A'}</span><br>
                            <span>Ημερομηνία Αποδοχής: ${member.accept_date || 'N/A'}</span><br>
                            <span>Ημερομηνία Απόρριψης: ${member.reject_date || 'N/A'}</span>
                            <hr>
                        `;
                    });
                } else {
                    modalContent.innerHTML = '<p>Δεν υπάρχουν μέλη επιτροπής για αυτή τη διπλωματική.</p>';
                }
    
                document.getElementById('details-modal').style.display = 'block';
                document.getElementById('modal-overlay').style.display = 'block';
            })
            .catch(error => {
                console.error('Σφάλμα:', error);
                alert('Προέκυψε πρόβλημα κατά τη φόρτωση των λεπτομερειών.');
                document.getElementById('details-modal').style.display = 'none';
                document.getElementById('modal-overlay').style.display = 'none';
            });
    }
    
    
    
    function closeModal() {
        // Κλείσιμο του modal
        document.getElementById('details-modal').style.display = 'none';
        document.getElementById('modal-overlay').style.display = 'none';
    }
    
    function cancelAssignment(thesesId) {
        if (!confirm("Είστε σίγουροι ότι θέλετε να ακυρώσετε την ανάθεση;")) {
            return;
        }
    
        fetch('http://localhost/web-project/pages/cancel_last.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ theses_id: thesesId }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Ανανέωση της λίστας διπλωματικών
                    filterTheses('under_assignment');
                } else {
                    alert('Σφάλμα: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Σφάλμα:', error);
                alert('Προέκυψε πρόβλημα κατά την ακύρωση της ανάθεσης.');
            });
    }
    
    function addNote(thesesId) {
        const note = prompt('Προσθέστε μια σημείωση (μέχρι 300 χαρακτήρες):');
        if (note && note.length <= 300) {
            fetch('http://localhost/web-project/pages/add_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ theses_id: thesesId, note: note }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Η σημείωση καταχωρήθηκε με επιτυχία.');
                    } else {
                        alert('Σφάλμα: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Σφάλμα:', error);
                    alert('Προέκυψε πρόβλημα κατά την καταχώρηση της σημείωσης.');
                });
        } else {
            alert('Η σημείωση πρέπει να είναι μέχρι 300 χαρακτήρες.');
        }
    }

    
    function changeStatusToUnderReview(thesesId) {
        if (confirm('Είστε σίγουροι ότι θέλετε να αλλάξετε την κατάσταση σε Υπό Εξέταση;')) {
            fetch('http://localhost/web-project/pages/change_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ theses_id: thesesId, status: 'active' }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Η κατάσταση άλλαξε με επιτυχία.');
                        // Ανανεώνει τη λίστα διπλωματικών
                        filterTheses('under_review');
                    } else {
                        alert('Σφάλμα: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Σφάλμα:', error);
                    alert('Προέκυψε πρόβλημα κατά την αλλαγή της κατάστασης.');
                });
        }
    }
    

    function cancelAssignmentWithReason(thesesId) {
        const assemblyNumber = prompt('Εισάγετε τον αριθμό της Γενικής Συνέλευσης:');
        const assemblyYear = prompt('Εισάγετε το έτος της Γενικής Συνέλευσης:');
        const reason = 'Από Διδάσκοντα';
    
        if (assemblyNumber && assemblyYear) {
            fetch('http://localhost/web-project/pages/cancel_active_assignment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    theses_id: thesesId,
                    assembly_number: assemblyNumber,
                    assembly_year: assemblyYear,
                    reason: reason,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Η ανάθεση ακυρώθηκε με επιτυχία.');
                        // Ανανεώνει τη λίστα διπλωματικών
                        filterTheses('active');
                    } else {
                        alert('Σφάλμα: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Σφάλμα:', error);
                    alert('Προέκυψε πρόβλημα κατά την ακύρωση της ανάθεσης.');
                });
        } else {
            alert('Παρακαλώ εισάγετε όλα τα στοιχεία.');
        }
    }
    
        
    function loadDraft(thesesId) {
        fetch('get_draft.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theses_id: thesesId })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('draft-content').textContent = data.success ? data.draft : 'Δεν υπάρχει πρόχειρο κείμενο.';
        });
    }
    
    function generatePresentationAnnouncement() {
        const thesisId = prompt('Εισάγετε το ID της διπλωματικής');
        fetch('generate_announcement.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theses_id: thesesId })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('announcement-content').textContent = data.success ? data.announcement : data.message;
        });
    }
    
    function submitGrade() {
        const thesisId = prompt('Εισάγετε το ID της διπλωματικής');
        const grade = prompt('Καταχωρήστε τη βαθμολογία');
        fetch('submit_grade.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theses_id: thesesId, grade: grade })
        })
        .then(response => response.json())
        .then(data => alert(data.message));
    }
    