<?php
/**
 * Purpose:        Parameterizes a database query
 *
 * Description:    Parameterizes an SQL query given a database connection, 
 *                 a query string, a data types string, and a variable number 
 *                 of parameters to be used in the query. If the query is 
 *                 successful, the database results object will be returned 
 *                 (or TRUE if no results set and the query was successful), 
 *                 otherwise FALSE is returned  and the connection has to be
 *                 queried for the last error.
 *
 * @param  $dbc              database connection
 * @param  $sql_query        SQL statement
 * @param  $data_types       string containing one character representing the data type for each parameter
 * @param  $query_parameters variable list of parameters representing each query parameter
 * @return string            Database results set, false if there is a database error, or true if successful.
 */
function parameterizedQuery($dbc, $sql_query, $data_types, ...$query_parameters)
{
    $ret_val = false;  // Assume failure

    if ($stmt = mysqli_prepare($dbc, $sql_query))
    {
        if (mysqli_stmt_bind_param($stmt, $data_types, ...$query_parameters)
                && mysqli_stmt_execute($stmt))
        {
            $ret_val = mysqli_stmt_get_result($stmt);

            if (!mysqli_errno($dbc) && !$ret_val)
            {
                $ret_val = true;
            }
        }       
    }
    return $ret_val;
}