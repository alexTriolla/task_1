<?php

require_once '../Services/CacheService.php';
require_once '../Services/LoggerService.php';
require_once '../Services/WeatherService.php';

// Initialize the services
$logger = new LoggerService('../Logs/error.log', '../Logs/info.log');
$cache = new CacheService('../Storage/weather_cache.json');
$weatherService = new WeatherService($cache, $logger);

// Initialize variables
$weatherData = null;
$displayData = null;
$error = null;
$city = '';
$filter = 'all';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reset'])) {
        // Reset button was clicked
        $weatherData = null;
        $displayData = null;
        $error = null;
        $city = '';
    } else {
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $filter = filter_input(INPUT_POST, 'filter', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!empty($city)) {
            try {
                // Get weather data using the WeatherService class
                $weatherData = $weatherService->getWeatherData($city);
                if ($filter === 'today') {
                    $displayData = $weatherService->getTodayWeather($weatherData);
                } elseif ($filter === 'tomorrow') {
                    $displayData = $weatherService->getTomorrowWeather($weatherData);
                } else {
                    $displayData = $weatherData['list'];
                }
            } catch (Exception $e) {
                // Capture the error message
                $error = $e->getMessage();
            }
        } else {
            // Handle case where city input is empty
            $error = 'Please enter a city name.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Weather Information</h1>
        <form action="" method="post" class="mb-6">
            <div class="flex items-center border-b border-blue-500 py-2">
                <input class="appearance-none bg-transparent border-none w-full text-gray-700 mr-3 py-1 px-2 leading-tight focus:outline-none" type="text" id="city" name="city" placeholder="Enter City Name" value="<?php echo htmlspecialchars($city); ?>" required>
                <button class="flex-shrink-0 bg-blue-500 hover:bg-blue-700 border-blue-500 hover:border-blue-700 text-sm border-4 text-white py-1 px-2 rounded" type="submit">
                    Get Weather
                </button>
            </div>
            <div class="mt-4 text-right">
                <button type="submit" name="reset" class="text-sm text-gray-600 hover:text-gray-800">Reset</button>
            </div>
            <div class="mt-4">
                <button type="submit" name="filter" value="today" class="bg-green-500 hover:bg-green-700 text-white py-1 px-2 rounded">Today</button>
                <button type="submit" name="filter" value="tomorrow" class="bg-yellow-500 hover:bg-yellow-700 text-white py-1 px-2 rounded">Tomorrow</button>
                <button type="submit" name="filter" value="all" class="bg-blue-500 hover:bg-blue-700 text-white py-1 px-2 rounded">All</button>
            </div>
        </form>
        <?php if (isset($displayData)): ?>
            <?php if ($filter === 'today' || $filter === 'tomorrow'): ?>
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Weather for <?php echo htmlspecialchars($city); ?></p>
                    <p><strong>Temperature:</strong> <?php echo round($displayData['main']['temp']); ?>°C / <?php echo $weatherService->cToFa($displayData['main']['temp']); ?>°F</p>
                    <p><strong>Humidity:</strong> <?php echo $displayData['main']['humidity']; ?>%</p>
                    <p><strong>Weather:</strong> <?php echo ucfirst($displayData['weather'][0]['description']); ?></p>
                    <p><strong>Wind Speed:</strong> <?php echo $displayData['wind']['speed']; ?> m/s</p>
                </div>
            <?php else: ?>
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Weather for <?php echo htmlspecialchars($city); ?></p>
                    <?php foreach ($displayData as $weather): ?>
                        <p><strong>Time:</strong> <?php echo $weather['dt_txt']; ?></p>
                        <p><strong>Temperature:</strong> <?php echo round($weather['main']['temp']); ?>°C / <?php echo $weatherService->cToFa($weather['main']['temp']); ?>°F</p>
                        <p><strong>Humidity:</strong> <?php echo $weather['main']['humidity']; ?>%</p>
                        <p><strong>Weather:</strong> <?php echo ucfirst($weather['weather'][0]['description']); ?></p>
                        <p><strong>Wind Speed:</strong> <?php echo $weather['wind']['speed']; ?> m/s</p>
                        <hr class="my-4">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php elseif (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p class="font-bold">Error</p>
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>