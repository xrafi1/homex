/**
 * Google Apps Script for USCA Website Form Handling
 * This script handles form submissions from the USCA website and stores data in Google Sheets
 */

// Configuration - Replace with your actual Google Sheet ID
const SHEET_ID = 'YOUR_GOOGLE_SHEET_ID_HERE';
const SHEET_NAME = 'USCA_Applications';

/**
 * Main function to handle HTTP POST requests from the website form
 */
function doPost(e) {
  try {
    // Enable CORS for cross-origin requests
    const response = {
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'POST, GET, OPTIONS',
        'Access-Control-Allow-Headers': 'Content-Type',
        'Content-Type': 'application/json'
      }
    };
    
    // Parse the incoming JSON data
    const data = JSON.parse(e.postData.contents);
    
    // Validate required fields
    if (!data.fullName || !data.email || !data.phone || !data.college) {
      return ContentService
        .createTextOutput(JSON.stringify({
          success: false,
          message: 'Missing required fields'
        }))
        .setMimeType(ContentService.MimeType.JSON);
    }
    
    // Add the data to the Google Sheet
    const result = addToSheet(data);
    
    if (result.success) {
      // Send confirmation email (optional)
      sendConfirmationEmail(data);
      
      return ContentService
        .createTextOutput(JSON.stringify({
          success: true,
          message: 'Application submitted successfully!'
        }))
        .setMimeType(ContentService.MimeType.JSON);
    } else {
      throw new Error(result.message);
    }
    
  } catch (error) {
    console.error('Error in doPost:', error);
    return ContentService
      .createTextOutput(JSON.stringify({
        success: false,
        message: 'Server error: ' + error.message
      }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

/**
 * Handle preflight OPTIONS requests for CORS
 */
function doGet(e) {
  return ContentService
    .createTextOutput(JSON.stringify({
      message: 'USCA Form Handler is running'
    }))
    .setMimeType(ContentService.MimeType.JSON);
}

/**
 * Add form data to Google Sheet
 */
function addToSheet(data) {
  try {
    // Open the Google Sheet
    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(SHEET_NAME);
    
    // If sheet doesn't exist, create it with headers
    if (!sheet) {
      const newSheet = SpreadsheetApp.openById(SHEET_ID).insertSheet(SHEET_NAME);
      const headers = [
        'Timestamp',
        'Full Name',
        'Email',
        'Phone Number',
        'College/University',
        'Status',
        'Notes'
      ];
      newSheet.getRange(1, 1, 1, headers.length).setValues([headers]);
      
      // Format header row
      const headerRange = newSheet.getRange(1, 1, 1, headers.length);
      headerRange.setBackground('#667eea');
      headerRange.setFontColor('white');
      headerRange.setFontWeight('bold');
      headerRange.setHorizontalAlignment('center');
      
      // Set column widths
      newSheet.setColumnWidth(1, 150); // Timestamp
      newSheet.setColumnWidth(2, 200); // Full Name
      newSheet.setColumnWidth(3, 250); // Email
      newSheet.setColumnWidth(4, 150); // Phone
      newSheet.setColumnWidth(5, 300); // College
      newSheet.setColumnWidth(6, 100); // Status
      newSheet.setColumnWidth(7, 200); // Notes
    }
    
    const targetSheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(SHEET_NAME);
    
    // Prepare the row data
    const timestamp = new Date();
    const rowData = [
      timestamp,
      data.fullName,
      data.email,
      data.phone,
      data.college,
      'New Application',
      ''
    ];
    
    // Add the data to the next available row
    targetSheet.appendRow(rowData);
    
    // Format the new row
    const lastRow = targetSheet.getLastRow();
    const dataRange = targetSheet.getRange(lastRow, 1, 1, rowData.length);
    
    // Alternate row colors for better readability
    if (lastRow % 2 === 0) {
      dataRange.setBackground('#f8f9fa');
    }
    
    // Format timestamp column
    targetSheet.getRange(lastRow, 1).setNumberFormat('yyyy-mm-dd hh:mm:ss');
    
    return {
      success: true,
      message: 'Data added successfully',
      row: lastRow
    };
    
  } catch (error) {
    console.error('Error adding to sheet:', error);
    return {
      success: false,
      message: 'Failed to add data to sheet: ' + error.message
    };
  }
}

/**
 * Send confirmation email to the applicant
 */
function sendConfirmationEmail(data) {
  try {
    const subject = 'Welcome to USCA - Application Received';
    const htmlBody = `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
          <h1 style="color: white; margin: 0;">United Student Council of Assam</h1>
          <p style="color: white; margin: 10px 0 0 0;">Empowering Manipuri Student Voices</p>
        </div>
        
        <div style="padding: 30px; background: #f8f9fa;">
          <h2 style="color: #2c3e50;">Dear ${data.fullName},</h2>
          
          <p style="color: #666; line-height: 1.6;">
            Thank you for your interest in joining the United Student Council of Assam (USCA)! 
            We have successfully received your application.
          </p>
          
          <div style="background: white; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <h3 style="color: #667eea; margin-top: 0;">Application Details:</h3>
            <p><strong>Name:</strong> ${data.fullName}</p>
            <p><strong>Email:</strong> ${data.email}</p>
            <p><strong>Phone:</strong> ${data.phone}</p>
            <p><strong>College/University:</strong> ${data.college}</p>
            <p><strong>Submitted:</strong> ${new Date().toLocaleString()}</p>
          </div>
          
          <p style="color: #666; line-height: 1.6;">
            Our team will review your application and contact you within 3-5 business days. 
            In the meantime, feel free to follow our activities and connect with fellow students.
          </p>
          
          <div style="text-align: center; margin: 30px 0;">
            <p style="color: #667eea; font-weight: bold;">
              "Uniting Manipuri Students for a Stronger Future"
            </p>
          </div>
          
          <p style="color: #666; font-size: 14px;">
            If you have any questions, please don't hesitate to contact us.
          </p>
          
          <p style="color: #666;">
            Best regards,<br>
            <strong>USCA Team</strong><br>
            United Student Council of Assam
          </p>
        </div>
        
        <div style="background: #2c3e50; padding: 20px; text-align: center;">
          <p style="color: white; margin: 0; font-size: 14px;">
            © 2025 United Student Council of Assam. All rights reserved.
          </p>
        </div>
      </div>
    `;
    
    // Send email to the applicant
    MailApp.sendEmail({
      to: data.email,
      subject: subject,
      htmlBody: htmlBody
    });
    
    // Send notification to USCA admin (optional)
    const adminEmail = 'admin@usca.org'; // Replace with actual admin email
    const adminSubject = 'New USCA Application Received';
    const adminBody = `
      New application received from:
      
      Name: ${data.fullName}
      Email: ${data.email}
      Phone: ${data.phone}
      College: ${data.college}
      
      Please review the application in the Google Sheet.
    `;
    
    // Uncomment the line below if you want admin notifications
    // MailApp.sendEmail(adminEmail, adminSubject, adminBody);
    
  } catch (error) {
    console.error('Error sending confirmation email:', error);
    // Don't throw error here as the main form submission was successful
  }
}

/**
 * Test function to verify the setup
 */
function testSetup() {
  const testData = {
    fullName: 'Test Student',
    email: 'test@example.com',
    phone: '+91 9876543210',
    college: 'Test University, Assam',
    timestamp: new Date().toISOString()
  };
  
  const result = addToSheet(testData);
  console.log('Test result:', result);
  
  if (result.success) {
    console.log('✅ Setup is working correctly!');
  } else {
    console.log('❌ Setup needs attention:', result.message);
  }
}

/**
 * Function to get all applications (for admin dashboard)
 */
function getAllApplications() {
  try {
    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(SHEET_NAME);
    if (!sheet) {
      return { success: false, message: 'Sheet not found' };
    }
    
    const data = sheet.getDataRange().getValues();
    const headers = data[0];
    const rows = data.slice(1);
    
    const applications = rows.map(row => {
      const app = {};
      headers.forEach((header, index) => {
        app[header] = row[index];
      });
      return app;
    });
    
    return {
      success: true,
      data: applications,
      count: applications.length
    };
    
  } catch (error) {
    console.error('Error getting applications:', error);
    return {
      success: false,
      message: error.message
    };
  }
}

/**
 * Function to update application status
 */
function updateApplicationStatus(email, status, notes = '') {
  try {
    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(SHEET_NAME);
    if (!sheet) {
      return { success: false, message: 'Sheet not found' };
    }
    
    const data = sheet.getDataRange().getValues();
    const emailColumn = 3; // Email is in column C (index 2, but 1-based for getRange)
    const statusColumn = 6; // Status is in column F
    const notesColumn = 7; // Notes is in column G
    
    for (let i = 1; i < data.length; i++) { // Start from 1 to skip header
      if (data[i][2] === email) { // Email is at index 2
        sheet.getRange(i + 1, statusColumn).setValue(status);
        if (notes) {
          sheet.getRange(i + 1, notesColumn).setValue(notes);
        }
        return { success: true, message: 'Status updated successfully' };
      }
    }
    
    return { success: false, message: 'Application not found' };
    
  } catch (error) {
    console.error('Error updating status:', error);
    return { success: false, message: error.message };
  }
}

