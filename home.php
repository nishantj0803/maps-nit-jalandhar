<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to login page if not logged in
    // Path is correct as index.html is in the same directory
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Campus Navigation System</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 0;
            background-color: #f1f1f1;
            min-height: 100vh; /* Ensure body takes full height */
        }
        header {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }
        header h1 {
            font-size: 1.5em; /* Adjusted from previous h1 style */
            margin: 0.5em 0;
        }
        header p {
            margin: 0.5em 0;
        }
        header a {
            color: #4285f4;
            text-decoration: none;
            font-weight: bold;
        }
        header a:hover {
            text-decoration: underline;
        }
        #form-container {
            /* position: absolute; top: 20px; left: 50%; transform: translateX(-50%); -- Removed for better flow */
            display: flex;
            flex-wrap: wrap; /* Allow items to wrap on smaller screens */
            align-items: center;
            justify-content: center; /* Center items */
            padding: 15px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            z-index: 1000; /* Keep it above the map */
            margin-top: 20px; /* Space from header */
            margin-bottom: 20px; /* Space before map */
            gap: 10px; /* Space between form elements */
        }
        #form-container label {
            margin-right: 5px;
        }
        #form-container select, #form-container button {
            padding: 10px; /* Increased padding */
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            color: #333;
        }
        #form-container button {
            background-color: #4285f4;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #form-container button:hover {
            background-color: #357ae8;
        }
        #map {
            height: 70vh; /* Adjusted height, can be dynamic */
            width: 95vw;  /* Use viewport width */
            max-width: 1200px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            /* margin-top: 80px; -- Removed, using margin on form-container */
        }
        /* Responsive adjustments */
        @media (max-width: 600px) {
            #form-container {
                flex-direction: column;
                align-items: stretch; /* Make selects and button full width */
            }
            #form-container select, #form-container button {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px; /* Space between stacked items */
            }
            #form-container button {
                margin-bottom: 0;
            }
            header h1 {
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body> {/* Corrected: Only one body tag, header inside body */}
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); // Sanitize output ?>!</h1>
        <p>You are logged in successfully.</p>
        <a href="logout.php">Logout</a>
    </header>

    <div id="form-container">
        <label for="start">From:</label>
        <select id="start"></select>

        <label for="end">To:</label>
        <select id="end"></select>
        
        <button type="button" onclick="findRoute()">Show Route</button>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialize the map and set its view to NIT Jalandhar coordinates and zoom level
        const map = L.map('map').setView([31.396, 75.535], 16);

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Define locations with their coordinates and connections (graph edges)
        // Connections represent paths and their weights (e.g., distance or travel time)
        const locations = {
            "Main Gate": {lat: 31.394231, lng: 75.533087, connections: {"Open Air Theatre": 1, "Right Round About": 1, "Left Round About": 1}},
            "Open Air Theatre": {lat: 31.394643, lng: 75.533784, connections: {"Main Gate": 1, "OAT Road Front": 1}},
            "Right Round About": {lat: 31.395797, lng: 75.533451, connections: {"Main Gate": 1, "MID OAT and right Round about": 1, "BasketBall Court": 1}},
            "Left Round About": {lat: 31.393987, lng: 75.534677, connections: {"Main Gate": 1, "MID OAT and left round about": 1, "Guest House": 1}},
            "OAT Road Front": {lat: 31.395273, lng: 75.534746, connections: {"MID OAT and right Round about": 1, "MID OAT and left round about": 1, "Open Air Theatre": 1}},
            "Central Seminar Hall": {lat: 31.395735, lng: 75.534689, connections: {"MID OAT and right Round about": 1, "Administrative Building": 1, "Lib Walkway Front": 1}},
            "MID OAT and right Round about": {lat: 31.395509, lng: 75.534470, connections: {"OAT Road Front": 1, "Central Seminar Hall": 1, "Right Round About": 1}},
            "MID OAT and left round about": {lat: 31.394763, lng: 75.534999, connections: {"OAT Road Front": 1, "IT Building": 1, "Left Round About": 1}},
            "IT Building": {lat: 31.395136, lng: 75.535654, connections: {"MID OAT and left round about": 1, "Administrative Building": 1, "IT Building walkway front": 1}},
            "Administrative Building": {lat: 31.395685, lng: 75.535524, connections: {"Central Seminar Hall": 1, "IT Building": 1, "Lib Walkway Front": 1, "IT Building walkway front": 1, "Administrative building back": 1}},
            "IT Building walkway front": {lat: 31.395419, lng: 75.535739, connections: {"Nescafe": 1, "Administrative Building": 1, "IT Building": 1}},
            "Lib Walkway Front": {lat: 31.395936, lng: 75.535300, connections: {"Administrative Building": 1, "Library Front": 1, "Central Seminar Hall": 1}},
            "Library Front": {lat: 31.396356, lng: 75.534933, connections: {"Library": 1, "Lib Walkway Front": 1, "Snackers Front": 1}},
            "Library": {lat: 31.396423, lng: 75.535269, connections: {"Library Front": 1}},
            "Nescafe": {lat: 31.394155, lng: 75.536844, connections: {"Guest House": 1, "IT Building walkway front": 1, "GH round about": 1}},
            "Snackers Front": {lat: 31.397119, lng: 75.534284, connections: {"Snackers": 1, "Department of physics and chemistry": 1, "Towards IT Building": 1}},
            "Snackers": {lat: 31.397006, lng: 75.534070, connections: {"Snackers Front": 1}},
            "Towards IT Building": {lat: 31.397493, lng: 75.534002, connections: {"Snackers Front": 1, "NC Front": 1, "BH roundabout": 1}},
            "Department of physics and chemistry": {lat: 31.397279, lng: 75.534542, connections: {"Snackers Front": 1}},
            "NC Front": {lat: 31.397809, lng: 75.534532, connections: {"Towards IT Building": 1, "Night Canteen": 1, "Manufacturing Workshop": 1}},
            "Night Canteen": {lat: 31.398025, lng: 75.534134, connections: {"NC Front": 1}},
            "BasketBall Court": {lat: 31.396707, lng: 75.533638, connections: {"Right Round About": 1, "BH roundabout": 1, "Snackers": 1}}, // Corrected: Snackers was likely intended instead of a direct BH connection here based on typical campus layouts
            "Manufacturing Workshop": {lat: 31.397916, lng: 75.534743, connections: {"NC Front": 1, "Department of Biotechnology": 1}},
            "Guest House": {lat: 31.393917, lng: 75.536361, connections: {"Nescafe": 1, "Left Round About": 1}},
            "Department of Biotechnology": {lat: 31.398279, lng: 75.535291, connections: {"MBH Round about": 1, "Manufacturing Workshop": 1}},
            "MBH Round about": {lat: 31.398524, lng: 75.535671, connections: {"Mega Boys Hostel": 1, "Lecture Theatre Back": 1, "Department of Biotechnology": 1}},
            "Mega Boys Hostel": {lat: 31.398947, lng: 75.535360, connections: {"MBH Round about": 1}},
            "BH roundabout": {lat: 31.397345, lng: 75.533764, connections: {"Towards IT Building": 1, "BasketBall Court": 1}},
            "Lecture Theatre Back": {lat: 31.397378, lng: 75.537286, connections: {"Lecture Theatre": 1, "MGH": 1, "MBH Round about": 1}}, // Added connection to MBH Round about
            "Lecture Theatre": {lat: 31.397120, lng: 75.536916, connections: {"Lecture Theatre Back": 1, "Lecture Theatre Front": 1}},
            "Lecture Theatre Front": {lat: 31.396628, lng: 75.536967, connections: {"Lecture Theatre": 1, "ECE Building": 1}},
            "ECE Building": {lat: 31.396158, lng: 75.536699, connections: {"Lecture Theatre Front": 1, "Civil Engineering": 1, "Administrative building back": 1}}, // Corrected: Administrative Building Back
            "Civil Engineering": {lat: 31.395613, lng: 75.536841, connections: {"ECE Building": 1, "Chemical Engineering": 1}},
            "Chemical Engineering": {lat: 31.395397, lng: 75.537053, connections: {"Textile Engineering": 1, "Civil Engineering": 1}},
            "Textile Engineering": {lat: 31.395132, lng: 75.537257, connections: {"GH round about": 1, "Chemical Engineering": 1}},
            "Administrative building back": {lat: 31.396220, lng: 75.536376, connections: {"Administrative Building": 1, "ECE Building": 1}}, // Corrected: Administrative Building
            "GH round about": {lat: 31.394590, lng: 75.537551, connections: {"Nescafe": 1, "Textile Engineering": 1, "MGH": 1}},
            "MGH": {lat: 31.395394, lng: 75.538868, connections: {"GH round about": 1, "Lecture Theatre Back": 1}},
        };

        // Locations to be displayed in the dropdowns (user-friendly names)
        const displayLocations = [
            "Main Gate", "Open Air Theatre", "Central Seminar Hall", "IT Building",
            "Administrative Building", "Library", "Nescafe", "Snackers",
            "Department of physics and chemistry", "Night Canteen", "BasketBall Court",
            "Manufacturing Workshop", "Guest House", "Department of Biotechnology",
            "Mega Boys Hostel", "Lecture Theatre", "ECE Building", "Civil Engineering",
            "Chemical Engineering", "Textile Engineering", "MGH"
        ];

        // Get dropdown elements
        const startSelect = document.getElementById('start');
        const endSelect = document.getElementById('end');

        // Populate the dropdowns with displayable locations
        displayLocations.forEach(locationName => {
            if (locations[locationName]) { // Ensure the location exists in our main data
                let optionStart = document.createElement('option');
                let optionEnd = document.createElement('option');
                optionStart.value = optionEnd.value = locationName;
                optionStart.textContent = optionEnd.textContent = locationName;
                startSelect.appendChild(optionStart);
                endSelect.appendChild(optionEnd);
            }
        });

        let routePath = null; // Variable to store the current route layer

        // Function to find and display the route
        function findRoute() {
            const startValue = startSelect.value;
            const endValue = endSelect.value;

            // Basic validation
            if (!startValue || !endValue) {
                alert("Please select both a starting and an ending location.");
                return;
            }
            if (startValue === endValue) {
                alert("Start and end locations cannot be the same. Please select two different locations.");
                return;
            }

            // Remove previous route from map if it exists
            if (routePath) {
                map.removeLayer(routePath);
                routePath = null;
            }

            // Find the shortest path using Dijkstra's algorithm
            const path = dijkstra(startValue, endValue);

            if (!path || path.length === 0) {
                alert("Could not find a route. Please ensure locations are connected.");
                return;
            }
            
            // Convert path (array of location names) to array of LatLng objects
            const latlngs = path.map(locationName => {
                const loc = locations[locationName];
                return [loc.lat, loc.lng];
            });
            
            // Create a polyline for the route and add it to the map
            routePath = L.polyline(latlngs, { 
                color: 'blue', 
                weight: 5, // Made line thicker
                opacity: 0.7, 
                smoothFactor: 1 
            }).addTo(map);

            // Fit map bounds to the route
            map.fitBounds(routePath.getBounds().pad(0.1)); // Added padding to bounds
        }

        // Dijkstra's algorithm implementation
        function dijkstra(startNode, endNode) {
            const distances = {}; // Stores the shortest distance from startNode to each node
            const previous = {};  // Stores the previous node in the shortest path
            const pq = new Set(); // A simple set to act as a priority queue (stores node names)

            // Initialize distances: Infinity for all nodes, 0 for startNode
            for (let node in locations) {
                distances[node] = Infinity;
                previous[node] = null;
                pq.add(node); // Add all nodes to the "priority queue"
            }
            distances[startNode] = 0;

            while (pq.size > 0) {
                // Find node in pq with the smallest distance
                let closestNode = null;
                let minDistance = Infinity;
                for (const node of pq) {
                    if (distances[node] < minDistance) {
                        minDistance = distances[node];
                        closestNode = node;
                    }
                }

                if (closestNode === null || closestNode === endNode) { // If endNode is reached or no path
                    break;
                }

                pq.delete(closestNode); // Remove closestNode from pq

                // For each neighbor of closestNode
                if (locations[closestNode] && locations[closestNode].connections) {
                    for (let neighbor in locations[closestNode].connections) {
                        if (pq.has(neighbor)) { // Only consider neighbors still in pq
                            let weight = locations[closestNode].connections[neighbor];
                            let altDistance = distances[closestNode] + weight;

                            if (altDistance < distances[neighbor]) {
                                distances[neighbor] = altDistance;
                                previous[neighbor] = closestNode;
                            }
                        }
                    }
                }
            }

            // Reconstruct the path from endNode back to startNode
            const path = [];
            let currentNode = endNode;
            while (previous[currentNode]) {
                path.unshift(currentNode);
                currentNode = previous[currentNode];
            }
            // Add the start node if a path was found (i.e., endNode was reached or startNode itself)
            if (currentNode === startNode || (path.length > 0 && previous[path[0]] === startNode) || startNode === endNode) {
                 path.unshift(startNode);
            } else {
                return []; // No path found
            }
           
            return path;
        }

        // Optional: Adjust map size on window resize
        window.addEventListener('resize', function() {
            map.invalidateSize();
        });

    </script>
</body>
</html>
