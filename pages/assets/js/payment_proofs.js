async function verifyProof(proofId) {
    if (!confirm('Verify this payment?')) return;
    
    const formData = new FormData();
    formData.append('action', 'verify');
    formData.append('proof_id', proofId);
    
    try {
        const response = await fetch('', { 
            method: 'POST', 
            body: formData 
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Payment verified!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to verify payment'));
        }
    } catch (err) {
        console.error('Error verifying payment:', err);
        alert('Error verifying payment. Check console for details.');
    }
}

async function rejectProof(proofId) {
    if (!confirm('Reject this payment?')) return;
    
    const formData = new FormData();
    formData.append('action', 'reject');
    formData.append('proof_id', proofId);
    
    try {
        const response = await fetch('', { 
            method: 'POST', 
            body: formData 
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Payment rejected!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to reject payment'));
        }
    } catch (err) {
        console.error('Error rejecting payment:', err);
        alert('Error rejecting payment. Check console for details.');
    }
}
