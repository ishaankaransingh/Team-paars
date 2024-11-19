<?php include('../includes.style.php'); ?>

        <!-- main content secion -->
        <div class="main-content">
            <div class="wrapper">
               <h1>Manage Admin</h1>
               <br>

               <?php 
               if(isset($_SESSION['add']))
               {
                     echo $_SESSION['add'];//display session message
                     unset($_SESSION['add']);//remove session message
               }

               if(isset($_SESSION['delete']))
               {
                  echo $_SESSION['delete'];
                  unset($_SESSION['delete']);
               }

               if(isset($_SESSION['update']))
               {
                  echo $_SESSION['update'];
                  unset($_SESSION['update']);
               }

               if(isset($_SESSION['user-not-found']))
               {
                  echo $_SESSION['user-not-found'];
                  unset($_SESSION['user-not-found']);
               }

               if(isset($_SESSION['pwd-not-match']))
               {
                  echo $_SESSION['pwd-not-match'];
                  unset($_SESSION['pwd-not-match']);
               }

               if(isset($_SESSION['change-pwd']))
               {
                  echo $_SESSION['change-pwd'];
                  unset($_SESSION['change-pwd']);
               }
               ?>
               <br><br>

               <!-- button voor adden van admin -->
                <a href="add-admin.php" class="btn btn-primary">Add Admin</a>

                <br><br>

               <table class="tbl-full-admin tbl-full">
                  <tr>
                     <th>Number</th>
                     <th>Full Name</th>
                     <th>Username</th>
                     <th>Actions</th>

                  </tr>

                  <?php 
                  //query to get all admin
                     $sql = "SELECT * FROM tbl_admin";
                     //execute the query
                     $res = mysqli_query($conn,$sql);

                     //check als de query is executed of niet
                     if($res==TRUE)
                     {
                        // count row om te checken als we data hebben in die database of niet
                        $count = mysqli_num_rows($res);//function om al die rows in je database te krijgen

                        $sn=1; //create a variable en assign een value

                        // aantal rows checken 
                        if($count>0)
                        {
                           //we hebben data in database
                           while($rows=mysqli_fetch_assoc($res))
                           {
                              // using while loop om alle data van database te krijgen
                              // en while loop will runnen zolang we data hebben in database

                              //get induviduele data
                              $id=$rows['id'];
                              $full_name=$rows['full_name'];
                              $username=$rows['username'];

                              // display value van table
                              ?>

                                    <tr>

                                    <td><?php echo $sn++; ?></td>
                                    <td><?php echo $full_name; ?></td>
                                    <td><?php echo $username; ?></td>
                                    <td>
                                       <a href="<?php echo SITEURL;  ?>admin/update-password.php?id=<?php echo $id; ?>" class="btn btn-primary">Change Password</a>
                                       <a href="<?php echo SITEURL;  ?>admin/update-admin.php?id=<?php echo $id; ?>"class="btn btn-secondary">Update Admin</a>
                                       <a href="<?php echo SITEURL;  ?>admin/delete-admin.php?id=<?php echo $id; ?>" class="btn btn-danger">Delete Admin</a>
                                    </td>

                                    </tr>

                              <?php
                           }
                        }
                        else{
                           //we hebben geen data in database
                        }
                     }
            
                  ?>

               </table>

              

               <div class="clearfix"></div>
            </div> 
         </div>

<?php include('partials/footer.php'); ?>

