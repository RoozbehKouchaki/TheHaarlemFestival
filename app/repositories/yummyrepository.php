<?php
require __DIR__ . '/repository.php';
require __DIR__ . '/../models/restaurant.php';
require __DIR__ . '/../models/session.php';
require __DIR__ . '/../models/reservation.php';
require __DIR__ . '/../models/page.php';
require __DIR__ . '/../models/pagecard.php';

class YummyRepository extends Repository
{
    function getFoodPageContent()
    {
        $stmt = $this->connection->prepare("SELECT page.id, images.image, page.title, page.description FROM `page`
                                            JOIN images ON page.headerImg = images.id
                                            WHERE page.id = 7");
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'page');
        return $stmt->fetch();
    }

    function getFoodPageCards()
    {
        $stmt = $this->connection->prepare("SELECT pagecard.id, pagecard.title, pagecard.description, pagecard.link, images.image
                                            FROM pagecard
                                            JOIN images ON pagecard.image = images.id
                                            WHERE pagecard.pageId = 7");
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'pagecard');
        return $stmt->fetchAll();
    }

    public function getRestaurants()
    {
        try {
            // Убираем лишние повторы "AS image2, img3.image AS image3"
            $stmt = $this->connection->prepare("
            SELECT 
                restaurant.id,
                restaurant.name,
                restaurant.location,
                restaurant.description,
                restaurant.cuisine,
                restaurant.seats,
                restaurant.stars,
                restaurant.email,
                restaurant.phonenumber,
                img1.image AS image1,
                img2.image AS image2,
                img3.image AS image3,
                restaurant.price
            FROM restaurant
            JOIN images img1 ON img1.id = restaurant.image1
            JOIN images img2 ON img2.id = restaurant.image2
            JOIN images img3 ON img3.id = restaurant.image3
        ");

            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'restaurant');
            $restaurants = $stmt->fetchAll();
            return $restaurants;
        } catch (PDOException $e) {
            echo $e;
        }
    }


    public function getRestaurantById($id)
    {
        try {
            $stmt = $this->connection->prepare("
            SELECT 
                restaurant.id,
                restaurant.name,
                restaurant.location,
                restaurant.description,
                restaurant.cuisine,
                restaurant.seats,
                restaurant.stars,
                restaurant.email,
                restaurant.phonenumber,
                img1.image AS image1,
                img2.image AS image2,
                img3.image AS image3,
                restaurant.price
            FROM restaurant
            JOIN images img1 ON img1.id = restaurant.image1
            JOIN images img2 ON img2.id = restaurant.image2
            JOIN images img3 ON img3.id = restaurant.image3
            WHERE restaurant.id = :id
        ");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'restaurant');

            $restaurant = $stmt->fetch();
            if (!$restaurant) {
                return null;
            }
            return $restaurant;

        } catch (PDOException $e) {
            echo $e;
        }
    }


    public function getRestaurantByIdAlt($id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM restaurant WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'restaurant');
            // Если не найдено, fetch вернёт false
            return $stmt->fetch();
        } catch (PDOException $e) {
            echo $e;
        }
    }

    public function saveRestaurant(Restaurant $restaurant)
    {
        try {
            if ($restaurant->getId() != 0) {
                // Обновление
                $stmt = $this->connection->prepare("
                UPDATE `restaurant`
                SET name = :name,
                    location = :location,
                    description = :description,
                    cuisine = :cuisine,
                    seats = :seats,
                    stars = :stars,
                    email = :email,
                    phonenumber = :phonenumber,
                    price = :price
                WHERE id = :id
            ");
                $stmt->bindValue(':id', $restaurant->getId(), PDO::PARAM_INT);
            } else {
                // Добавление
                $stmt = $this->connection->prepare("
                INSERT INTO `restaurant`
                   (name, location, description, cuisine, seats, stars, email, phonenumber, price, image1, image2, image3)
                VALUES
                   (:name, :location, :description, :cuisine, :seats, :stars, :email, :phonenumber, :price, :image1, :image2, :image3)
            ");
                $stmt->bindValue(':image1', $restaurant->getImage1(), PDO::PARAM_INT);
                $stmt->bindValue(':image2', $restaurant->getImage2(), PDO::PARAM_INT);
                $stmt->bindValue(':image3', $restaurant->getImage3(), PDO::PARAM_INT);
            }

            // Общие bindValue для обоих случаев:
            $stmt->bindValue(':name',        $restaurant->getName());
            $stmt->bindValue(':location',    $restaurant->getLocation());
            $stmt->bindValue(':description', $restaurant->getDescription());
            $stmt->bindValue(':cuisine',     $restaurant->getCuisine());
            $stmt->bindValue(':seats',       $restaurant->getSeats(), PDO::PARAM_INT);
            $stmt->bindValue(':stars',       $restaurant->getStars(), PDO::PARAM_INT);
            $stmt->bindValue(':email',       $restaurant->getEmail());
            $stmt->bindValue(':phonenumber', $restaurant->getPhonenumber());
            // Вот это — ключевое! Привязываем price к :price
            $stmt->bindValue(':price',       $restaurant->getPrice());

            $stmt->execute();
        } catch (PDOException $e) {
            echo ($e);
        }
    }

    public function saveImage(string $imgData)
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO `images` (image) VALUES (:image)");
            $stmt->bindParam(':image', $imgData);
            $stmt->execute();
            return $this->connection->lastInsertId();
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function updateImage(string $imgData, int $id)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE `images` SET image = :image WHERE id = :id");
            $stmt->bindValue(':image', $imgData);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $id;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    public function deleteRestaurant()
    {
        $restaurantid = htmlspecialchars($_GET['restaurantid']);
        try {
            $stmt = $this->connection->prepare("DELETE FROM `restaurant` WHERE id = :id");
            $stmt->bindParam(':id', $restaurantid);
            $stmt->execute();
        } catch (PDOException $e) {
            echo ($e);
        }
    }

    public function getSessions()
    {
        try {
            $stmt = $this->connection->prepare("SELECT fs.id, fs.restaurantid, restaurant.name AS restaurantname, fs.price, fs.reducedprice, fs.starttime,
                                                fs.session_length, fs.available_seats FROM `food_session` AS fs 
                                                JOIN restaurant ON fs.restaurantid = restaurant.id");
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'session');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            echo $e;
        }
    }

    public function getSessionById($sessionid)
    {
        try {
            $stmt = $this->connection->prepare("SELECT fs.id, fs.restaurantid, restaurant.name AS restaurantname, fs.price, fs.reducedprice, fs.starttime,
                                                fs.session_length, fs.available_seats FROM `food_session` AS fs 
                                                JOIN restaurant ON fs.restaurantid = restaurant.id
                                                WHERE fs.id = :id");
            $stmt->bindParam(':id', $sessionid);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'session');
            return $stmt->fetch();
        } catch (PDOException $e) {
            echo $e;
        }
    }

    public function getSessionsForRestaurant()
    {
        $id = htmlspecialchars($_GET["restaurantid"]);
        try {
            $stmt = $this->connection->prepare("SELECT fs.id, fs.restaurantid, restaurant.name AS restaurantname, fs.price, fs.reducedprice, fs.starttime,
                                                fs.session_length, fs.available_seats FROM `food_session` AS fs 
                                                JOIN restaurant ON fs.restaurantid = restaurant.id
                                                WHERE fs.restaurantid = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'session');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            echo $e;
        }
    }

    public function saveSession(Session $session)
    {
        try {
            if ($session->getId() != 0) {
                $stmt = $this->connection->prepare("UPDATE `food_session` SET restaurantid = :restaurantid, price = :price, reducedprice = :reducedprice, 
                                                    starttime = :starttime, session_length = :session_length, available_seats = :available_seats 
                                                    WHERE id = :id");
                $stmt->bindValue(':id', $session->getId());
            } else {
                $stmt = $this->connection->prepare("INSERT INTO `food_session` (restaurantid, price, reducedprice, 
                                                    starttime, session_length, available_seats) VALUES (:restaurantid, :price, 
                                                    :reducedprice, :starttime, :session_length, :available_seats)");
            }

            $stmt->bindValue(':restaurantid', $session->getRestaurantid());
            $stmt->bindValue(':price', $session->getPrice());
            $stmt->bindValue(':reducedprice', $session->getReducedprice());
            $stmt->bindValue(':starttime', $session->getStarttime());
            $stmt->bindValue(':session_length', $session->getSession_length());
            $stmt->bindValue(':available_seats', $session->getAvailable_seats());

            $stmt->execute();
        } catch (PDOException $e) {
            echo ($e);
        }
    }

    public function deleteSession()
    {
        $sessionid = htmlspecialchars($_GET['sessionid']);
        try {
            $stmt = $this->connection->prepare("DELETE FROM `food_session` WHERE id = :id");
            $stmt->bindParam(':id', $sessionid);
            $stmt->execute();
        } catch (PDOException $e) {
            echo ($e);
        }
    }

    public function getReservations()
    {
        try {
            $stmt = $this->connection->prepare("SELECT reservation.id, reservation.name, reservation.restaurantID, 
                                                restaurant.name AS restaurantName, reservation.sessionID, reservation.seats, reservation.date, reservation.request, 
                                                reservation.price, reservation.status
                                                FROM reservation
                                                JOIN restaurant ON reservation.restaurantID = restaurant.id");
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'reservation');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            echo $e;
        }
    }

    public function deactivateReservation()
    {
        $reservationid = htmlspecialchars($_GET['reservationid']);
        try {
            $stmt = $this->connection->prepare("UPDATE reservation SET status = FALSE WHERE id = :id");
            $stmt->bindParam(':id', $reservationid);
            $stmt->execute();
        } catch (PDOException $e) {
            echo ($e);
        }
    }

    public function addReservation(Reservation $reservation)
    {
        $stmt = $this->connection->prepare("INSERT INTO `reservation` (`name`, `restaurantID`, `sessionID`, `seats`, `date`,
                                            `request`, `price`, `status`) VALUES (:name, :restaurantID, :sessionID, :seats,
                                            :date, :request, :price, 1 )");
        $stmt->bindValue(':name', $reservation->getName());
        $stmt->bindValue(':restaurantID', $reservation->getRestaurantID());
        $stmt->bindValue(':sessionID', $reservation->getSessionID());
        $stmt->bindValue(':seats', $reservation->getSeats());
        $stmt->bindValue(':date', $reservation->getDate());
        $stmt->bindValue(':request', $reservation->getRequest());
        $stmt->bindValue(':price', $reservation->getPrice());
        $stmt->execute();
    }

    public function getReservationIdByName($name)
    {
        $stmt = $this->connection->prepare("SELECT id from reservation WHERE name=:name");
        $stmt->bindValue(':name', $name);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Reservation');
        $reservation = $stmt->fetch();
        return $reservation->getId();
    }
}
