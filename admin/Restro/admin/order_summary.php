<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');

if (isset($_POST['view_order'])) {
    $order_id = $_POST['order_id'];

    // Fetch the order details including customer name
    $stmt = $mysqli->prepare("SELECT * FROM rpos_orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_object();

    if ($order) {
        // Fetch all the orders made by the customer
        $stmt = $mysqli->prepare("SELECT * FROM rpos_orders WHERE customer_name = ?");
        $stmt->bind_param("s", $order->customer_name);
        $stmt->execute();
        $orders_result = $stmt->get_result();

        // Start HTML output
        ?>
        <html>
        <body>
        <style>
            .main-content {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }

            .card {
                width: 400px;
                margin: 0 auto; /* Center the card horizontally */
                padding: 20px; /* Add some padding */
                text-align: center;
            }
        </style>
        <!-- Main content -->
        <div class="main-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <center><h2>Customer Orders</h2></center>
                        <div class="card shadow">
                            <div class="card-body">
                                <h3>Orders Summary</h3>
                                <p><strong>Customer:</strong> <?php echo $order->customer_name; ?></p>
                                <hr>
                                <p><strong>Products:</strong>
                                    <?php while ($order_row = $orders_result->fetch_object()) { 
                                        echo $order_row->prod_name . ', ';
                                    } ?>
                                </p>
                                <p><strong>Unit Price:</strong>
                                    <?php $orders_result->data_seek(0);
                                    while ($order_row = $orders_result->fetch_object()) {
                                        echo '$' . $order_row->prod_price . ', ';
                                    } ?>
                                </p>
                                <p><strong>Quantity:</strong>
                                    <?php $orders_result->data_seek(0);
                                    while ($order_row = $orders_result->fetch_object()) {
                                        echo $order_row->prod_qty . ' ' . $order_row->prod_name . ', ';
                                    } ?>
                                </p>
                                <p><strong>Total Price:</strong>
                                    <?php $orders_result->data_seek(0);
                                    $total_price = 0;
                                    while ($order_row = $orders_result->fetch_object()) {
                                        $total_price += $order_row->prod_price * $order_row->prod_qty;
                                    }
                                    echo '$' . $total_price;
                                    ?>
                                </p>
                                <p><strong>Status:</strong>
                                    <?php if ($order) {
                                        echo $order->order_status;
                                    } ?>
                                </p>
                                <form action="update_order_status.php?order_id=<?php echo $order->order_id; ?>" method="POST">
                                    <select name="new_status" class="form-control">
                                        <option value="">Select Status</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
    } else {
        $_SESSION['error'] = "Order not found";
        header("Location: orders_reports.php"); // Redirect back to the order list page
        exit();
    }
}
?>
