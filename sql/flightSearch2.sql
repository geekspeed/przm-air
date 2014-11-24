-- Stored Procedure created by Marc Hayes <marc.hayes.tech@gmail.com>
-- for use by PRZM capstone group

-- Drop procedure if it exists
DROP PROCEDURE IF EXISTS spFlightSearchR;

-- Create Procedure
CREATE PROCEDURE spFlightSearchR (IN startLoc VARCHAR(20), endLoc VARCHAR(20), departDate DATETIME, arrivalDate DATETIME, minTicket INT, layOver INT)
		PROC:BEGIN
		-- Stored Procedure created by Marc Hayes <marc.hayes.tech@gmail.com>
		-- for use by PRZM capstone group

		-- ensure that arrival date is after departure date, if not throw error and exit proc
		IF departDate >= arrivalDate THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Arrival date must be AFTER Departure date.';
			LEAVE PROC;
		END IF;

		-- create temporary work table
		CREATE TEMPORARY TABLE flightSearchR (
			startId INT,
			endId INT,
			path VARCHAR(256),
			INDEX (startId),
			INDEX (endId),
			INDEX (path)
		);

		-- Populate initial nodes based on range of dates and origin
		INSERT INTO flightSearchR
			SELECT flightId, flightId, flightId
			FROM flight
			WHERE origin = startLoc -- start at the right location
					AND departureDateTime >= departDate -- ensure that the date range of seed records is valid
					AND arrivalDateTime <= arrivalDate -- ensure that the date range of seed records is valid
					AND totalSeatsOnPlane >= minTicket; -- ensure we are only looking at flights that have enough available tickets

		-- Loop through original results finding all nodes that originate from the arrival of prior loop
		-- exit loop when no more relevant paths are found in the time window
		REPEAT
			INSERT INTO flightSearchR
				SELECT R1.startId, F2.flightId, CONCAT(R1.path, ',', CAST(F2.flightId AS CHAR))
				FROM flightSearchR R1
					INNER JOIN flight F1 ON R1.endId = F1.flightId
					INNER JOIN flight F2 ON F1.destination = F2.origin AND DATE_ADD(F1.arrivalDateTime, INTERVAL layOver MINUTE) <= F2.departureDateTime
					LEFT JOIN flightSearchR R2 ON R2.path = CONCAT(R1.path, ',', CAST(F2.flightId AS CHAR))
				WHERE F1.destination <> endLoc -- do no process paths that have already terminated at Destination
						AND F2.totalSeatsOnPlane >= minTicket -- ensure we are only looking at flights that have enough available tickets
						AND F2.arrivalDateTime <= arrivalDate -- ensure we are not grabbing "nodes" that are outside of time range
						AND R2.path IS NULL; -- ensure we are not processing nodes that have already been processed.
		UNTIL (ROW_COUNT() = 0) -- exit when prior iteration has returned 0 results
		END REPEAT
		;

		-- Return only paths with ending nodes at destination, include stop count
		SELECT R.startId, R.endId, R.path, CHAR_LENGTH(path) - CHAR_LENGTH(REPLACE(path,',','')) AS Stops
		FROM flightSearchR R
			INNER JOIN flight F ON F.flightId = R.endId
		WHERE F.destination = endLoc
		ORDER BY Stops;
	END;